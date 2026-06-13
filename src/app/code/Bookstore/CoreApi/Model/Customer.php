<?php

namespace Bookstore\CoreApi\Model;

use Bookstore\CoreApi\Api\CustomerInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\AuthorizationException;
use Bookstore\CoreApi\Model\Data\CustomerMe;

class Customer implements CustomerInterface
{
    public function __construct(
        private readonly Session $session,
        private readonly CustomerRepositoryInterface $customerRepository,
        private ?UserContextInterface $userContext = null
    ) {
        $this->userContext = $this->userContext ?: ObjectManager::getInstance()->get(UserContextInterface::class);
    }

    public function me()
    {
        $customerId = (int)$this->userContext->getUserId();
        if (!$customerId || (int)$this->userContext->getUserType() !== UserContextInterface::USER_TYPE_CUSTOMER) {
            throw new AuthorizationException(__('Customer token is required.'));
        }

        $customer = $this->customerRepository->getById($customerId);

        return (new CustomerMe())
            ->setLoggedIn(true)
            ->setId((int)$customer->getId())
            ->setEmail((string)$customer->getEmail())
            ->setFirstname((string)$customer->getFirstname())
            ->setLastname((string)$customer->getLastname());
    }
}
