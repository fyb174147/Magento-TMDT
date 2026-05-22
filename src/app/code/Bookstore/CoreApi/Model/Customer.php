<?php

namespace Bookstore\CoreApi\Model;

use Bookstore\CoreApi\Api\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;

class Customer implements CustomerInterface
{
    public function __construct(
        private readonly Session $session,
        private readonly CustomerRepositoryInterface $customerRepository
    ) {
    }

    public function me()
    {
        if (!$this->session->isLoggedIn()) {
            return ['logged_in' => false];
        }

        $customer = $this->customerRepository->getById((int)$this->session->getCustomerId());

        return [
            'logged_in' => true,
            'id' => (int)$customer->getId(),
            'email' => (string)$customer->getEmail(),
            'firstname' => (string)$customer->getFirstname(),
            'lastname' => (string)$customer->getLastname(),
        ];
    }
}
