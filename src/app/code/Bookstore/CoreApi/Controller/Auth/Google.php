<?php
declare(strict_types=1);

namespace Bookstore\CoreApi\Controller\Auth;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Google implements HttpGetActionInterface, HttpPostActionInterface
{
    private const SESSION_STATE_KEY = 'bookstore_google_oauth_state';
    private const SESSION_PENDING_PROFILE_KEY = 'bookstore_google_pending_profile';
    private const AUTH_ENDPOINT = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_ENDPOINT = 'https://oauth2.googleapis.com/token';
    private const USERINFO_ENDPOINT = 'https://openidconnect.googleapis.com/v1/userinfo';

    public function __construct(
        private readonly RequestInterface $request,
        private readonly RedirectFactory $redirectFactory,
        private readonly RawFactory $rawFactory,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly UrlInterface $url,
        private readonly Curl $curl,
        private readonly Json $json,
        private readonly ManagerInterface $messageManager,
        private readonly Session $customerSession,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly CustomerFactory $customerFactory,
        private readonly AccountManagementInterface $accountManagement,
        private readonly StoreManagerInterface $storeManager,
        private readonly FormKey $formKey
    ) {
    }

    public function execute(): ResultInterface
    {
        if (!$this->getClientId() || !$this->getClientSecret()) {
            return $this->fail(__('Google login is not configured.'));
        }

        if ($this->request->getMethod() === 'POST' && $this->request->getParam('password_setup')) {
            return $this->handlePasswordSetup();
        }

        if (!$this->request->getParam('code')) {
            $pendingProfile = $this->customerSession->getData(self::SESSION_PENDING_PROFILE_KEY);
            if (is_array($pendingProfile) && !empty($pendingProfile['email'])) {
                return $this->renderPasswordSetupPopup($pendingProfile);
            }

            return $this->redirectToGoogle();
        }

        return $this->handleCallback();
    }

    private function redirectToGoogle(): Redirect
    {
        $state = bin2hex(random_bytes(16));
        $this->customerSession->setData(self::SESSION_STATE_KEY, $state);

        $params = [
            'client_id' => $this->getClientId(),
            'redirect_uri' => $this->getCallbackUrl(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account'
        ];

        return $this->redirectFactory->create()->setUrl(self::AUTH_ENDPOINT . '?' . http_build_query($params));
    }

    private function handleCallback(): ResultInterface
    {
        if (!$this->isValidState((string)$this->request->getParam('state'))) {
            return $this->fail(__('Invalid Google login session.'));
        }

        try {
            $token = $this->requestAccessToken((string)$this->request->getParam('code'));
            $profile = $this->requestUserProfile((string)($token['access_token'] ?? ''));

            if (empty($profile['email']) || empty($profile['email_verified'])) {
                return $this->fail(__('Google account email is not verified.'));
            }

            $customer = $this->getExistingCustomer($profile);
            if (!$customer) {
                $this->customerSession->setData(self::SESSION_PENDING_PROFILE_KEY, $profile);

                return $this->renderPasswordSetupPopup($profile);
            }

            $this->customerSession->setCustomerDataAsLoggedIn($customer);
            $this->customerSession->regenerateId();
            $this->messageManager->addSuccessMessage(__('You are now logged in with Google.'));

            return $this->redirectFactory->create()->setPath('customer/account');
        } catch (\Throwable $exception) {
            return $this->fail(__('Unable to login with Google. Please try again.'));
        }
    }

    private function handlePasswordSetup(): ResultInterface
    {
        $profile = $this->customerSession->getData(self::SESSION_PENDING_PROFILE_KEY);
        if (!is_array($profile) || empty($profile['email'])) {
            return $this->fail(__('Google login session expired. Please try again.'));
        }

        $password = (string)$this->request->getParam('password');
        $confirmation = (string)$this->request->getParam('password_confirmation');
        if ($password === '' || $password !== $confirmation) {
            return $this->renderPasswordSetupPopup($profile, 'Xác nhận mật khẩu không trùng khớp.');
        }

        try {
            $customer = $this->createCustomerFromGoogleProfile($profile, $password);
            $this->customerSession->unsetData(self::SESSION_PENDING_PROFILE_KEY);
            $this->customerSession->setCustomerDataAsLoggedIn($customer);
            $this->customerSession->regenerateId();
            $this->messageManager->addSuccessMessage(__('Your account has been created and you are now logged in.'));

            return $this->redirectFactory->create()->setPath('customer/account');
        } catch (\Throwable) {
            return $this->renderPasswordSetupPopup($profile, 'Mật khẩu chưa đạt yêu cầu của Magento. Vui lòng thử mật khẩu khác.');
        }
    }

    private function requestAccessToken(string $code): array
    {
        $this->curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->curl->post(self::TOKEN_ENDPOINT, http_build_query([
            'code' => $code,
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'redirect_uri' => $this->getCallbackUrl(),
            'grant_type' => 'authorization_code'
        ]));

        $token = $this->json->unserialize($this->curl->getBody());
        if ($this->curl->getStatus() !== 200 || empty($token['access_token'])) {
            throw new \RuntimeException('Google token exchange failed.');
        }

        return $token;
    }

    private function requestUserProfile(string $accessToken): array
    {
        if ($accessToken === '') {
            throw new \RuntimeException('Missing Google access token.');
        }

        $this->curl->addHeader('Authorization', 'Bearer ' . $accessToken);
        $this->curl->get(self::USERINFO_ENDPOINT);

        $profile = $this->json->unserialize($this->curl->getBody());
        if ($this->curl->getStatus() !== 200 || empty($profile['email'])) {
            throw new \RuntimeException('Google userinfo request failed.');
        }

        return $profile;
    }

    private function getExistingCustomer(array $profile): ?\Magento\Customer\Api\Data\CustomerInterface
    {
        $websiteId = (int)$this->storeManager->getWebsite()->getId();
        $email = (string)$profile['email'];

        try {
            return $this->customerRepository->get($email, $websiteId);
        } catch (NoSuchEntityException) {
            return null;
        }
    }

    private function createCustomerFromGoogleProfile(
        array $profile,
        string $password
    ): \Magento\Customer\Api\Data\CustomerInterface {
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId((int)$this->storeManager->getWebsite()->getId());
        $customer->setEmail((string)$profile['email']);
        $customer->setFirstname($this->normalizeName((string)($profile['given_name'] ?? 'Google')));
        $customer->setLastname($this->normalizeName((string)($profile['family_name'] ?? 'User')));

        return $this->accountManagement->createAccount($customer->getDataModel(), $password);
    }

    private function isValidState(string $state): bool
    {
        $expected = (string)$this->customerSession->getData(self::SESSION_STATE_KEY);
        $this->customerSession->unsetData(self::SESSION_STATE_KEY);

        return $state !== '' && $expected !== '' && hash_equals($expected, $state);
    }

    private function getCallbackUrl(): string
    {
        return $this->url->getUrl('shop/auth/google', ['_secure' => true]);
    }

    private function getClientId(): string
    {
        return (string)($this->getConfigValue('bookstore_google_login/general/client_id') ?: getenv('GOOGLE_CLIENT_ID'));
    }

    private function getClientSecret(): string
    {
        return (string)($this->getConfigValue('bookstore_google_login/general/client_secret') ?: getenv('GOOGLE_CLIENT_SECRET'));
    }

    private function getConfigValue(string $path): ?string
    {
        $value = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function normalizeName(string $name): string
    {
        $name = trim($name);

        return $name !== '' ? $name : 'Google';
    }

    private function fail(\Stringable|string $message): Redirect
    {
        $this->messageManager->addErrorMessage($message);

        return $this->redirectFactory->create()->setPath('customer/account/login');
    }

    private function renderPasswordSetupPopup(array $profile, string $error = ''): Raw
    {
        $email = htmlspecialchars((string)$profile['email'], ENT_QUOTES, 'UTF-8');
        $action = htmlspecialchars($this->url->getUrl('shop/auth/google', ['_secure' => true]), ENT_QUOTES, 'UTF-8');
        $formKey = htmlspecialchars($this->formKey->getFormKey(), ENT_QUOTES, 'UTF-8');
        $error = htmlspecialchars($error, ENT_QUOTES, 'UTF-8');
        $errorHtml = $error !== '' ? '<div class="error">' . $error . '</div>' : '';
        $minLength = max(8, (int)$this->getConfigValue('customer/password/minimum_password_length'));
        $requiredClasses = max(1, (int)$this->getConfigValue('customer/password/required_character_classes_number'));

        $html = <<<HTML
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tạo mật khẩu BookStore</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: Arial, sans-serif; background: #f8fafc; color: #1e293b; }
        .auth-overlay { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; background: rgba(15, 23, 42, .12); }
        .auth-modal { width: min(460px, 100%); background: #fff; border: 2px solid #0095ff; border-radius: 14px; padding: 28px; box-shadow: 0 20px 60px rgba(15, 23, 42, .18); }
        h1 { margin: 0 0 10px; font-size: 24px; line-height: 1.25; font-weight: 700; }
        p { margin: 0 0 20px; color: #64748b; line-height: 1.5; }
        label { display: block; margin: 14px 0 8px; font-weight: 700; font-size: 14px; }
        input { width: 100%; height: 44px; border: 1px solid #dbe3ef; border-radius: 8px; padding: 0 12px; font-size: 14px; outline: none; }
        input:focus { border-color: #0095ff; box-shadow: 0 0 0 3px rgba(0, 149, 255, .12); }
        .rules { margin: 14px 0 20px; padding: 0; list-style: none; display: grid; gap: 8px; color: #64748b; font-size: 13px; }
        .rules li.ok { color: #059669; font-weight: 700; }
        .error { margin: 0 0 16px; padding: 10px 12px; border-radius: 8px; background: #fef2f2; color: #dc2626; font-size: 13px; font-weight: 700; }
        .actions { display: flex; gap: 10px; align-items: center; margin-top: 18px; }
        button { flex: 1; height: 44px; border: 0; border-radius: 8px; background: #0095ff; color: #fff; font-weight: 800; cursor: pointer; }
        button:disabled { opacity: .45; cursor: not-allowed; }
        a { color: #64748b; text-decoration: none; font-weight: 700; font-size: 13px; }
    </style>
</head>
<body>
    <div class="auth-overlay">
        <form class="auth-modal" method="post" action="{$action}">
            <input type="hidden" name="form_key" value="{$formKey}">
            <input type="hidden" name="password_setup" value="1">
            <h1>Tạo mật khẩu</h1>
            <p>Tài khoản Google <strong>{$email}</strong> chưa có trong BookStore. Hãy tạo mật khẩu để hoàn tất đăng ký.</p>
            {$errorHtml}

            <label for="password">Mật khẩu</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>

            <label for="password_confirmation">Xác nhận mật khẩu</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>

            <ul class="rules">
                <li id="rule-length">Tối thiểu {$minLength} ký tự</li>
                <li id="rule-classes">Đạt ít nhất {$requiredClasses} nhóm ký tự: chữ thường, chữ hoa, số, ký tự đặc biệt</li>
                <li id="rule-match">Xác nhận mật khẩu trùng khớp</li>
            </ul>

            <div class="actions">
                <button id="submit" type="submit" disabled>Tạo tài khoản</button>
                <a href="/customer/account/login">Hủy</a>
            </div>
        </form>
    </div>
    <script>
        const minLength = {$minLength};
        const requiredClasses = {$requiredClasses};
        const password = document.getElementById('password');
        const confirmation = document.getElementById('password_confirmation');
        const submit = document.getElementById('submit');
        const ruleLength = document.getElementById('rule-length');
        const ruleClasses = document.getElementById('rule-classes');
        const ruleMatch = document.getElementById('rule-match');

        function countClasses(value) {
            return [/[a-z]/, /[A-Z]/, /[0-9]/, /[^a-zA-Z0-9]/].reduce((count, regex) => count + (regex.test(value) ? 1 : 0), 0);
        }

        function setRule(element, valid) {
            element.classList.toggle('ok', valid);
        }

        function validate() {
            const value = password.value;
            const lengthOk = value.length >= minLength;
            const classesOk = countClasses(value) >= requiredClasses;
            const matchOk = value !== '' && value === confirmation.value;

            setRule(ruleLength, lengthOk);
            setRule(ruleClasses, classesOk);
            setRule(ruleMatch, matchOk);
            submit.disabled = !(lengthOk && classesOk && matchOk);
        }

        password.addEventListener('input', validate);
        confirmation.addEventListener('input', validate);
    </script>
</body>
</html>
HTML;

        return $this->rawFactory->create()->setContents($html);
    }
}
