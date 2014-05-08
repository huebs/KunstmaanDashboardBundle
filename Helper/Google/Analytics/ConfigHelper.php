<?php

namespace Kunstmaan\DashboardBundle\Helper\Google\Analytics;
use Kunstmaan\DashboardBundle\Helper\Google\Analytics\ServiceHelper;
use Doctrine\ORM\EntityManager;

class ConfigHelper
{

    /** @var ServiceHelper */
    private $serviceHelper;

    /** @var string $token */
    private $token = false;

    /** @var string $accountId */
    private $propertyId = false;

    /** @var string $accountId */
    private $accountId = false;

    /** @var string $profileId */
    private $profileId = false;

    /** @var EntityManager $em */
    private $em;

    /**
     * constructor
     *
     * @param ServiceHelper $serviceHelper
     * @param EntityManager $em
     */
    public function __construct(ServiceHelper $serviceHelper, EntityManager $em) {
        $this->serviceHelper = $serviceHelper;
        $this->em = $em;
        $this->init();
    }


    /**
     * Tries to initialise the Client object
     *
     * @param int $configId
     */
    public function init($configId=false)
    {
        // if token is already saved in the database
        if ($this->getToken($configId) && '' !== $this->getToken($configId)) {
            $this
                ->serviceHelper
                ->getClientHelper()
                ->getClient()
                ->setAccessToken($this->getToken($configId));
        }

        if ($configId) {
            $this->getAccountId($configId);
            $this->getPropertyId($configId);
            $this->getProfileId($configId);
        }
    }


    /* =============================== TOKEN =============================== */

        /**
         * Get the token from the database
         *
         * @return string $token
         */
        private function getToken($configId=false)
        {
            if (!$this->token || $configId) {
                /** @var AnalyticsConfigRepository $analyticsConfigRepository */
                $analyticsConfigRepository = $this->em->getRepository('KunstmaanDashboardBundle:AnalyticsConfig');
                $this->token = $analyticsConfigRepository->getConfig($configId)->getToken();
            }

            return $this->token;
        }

        /**
         * Save the token to the database
         */
        public function saveToken($token, $configId=false)
        {
            $this->token = $token;
            $this->em->getRepository('KunstmaanDashboardBundle:AnalyticsConfig')->saveToken($token, $configId);
        }

        /**
         * Check if token is set
         *
         * @return boolean $result
         */
        public function tokenIsSet()
        {
            return $this->getToken() && '' !== $this->getToken();
        }

    /* =============================== ACCOUNT =============================== */

        /**
         * Get a list of all available accounts
         *
         * @return array $data A list of all properties
         */
        public function getAccounts()
        {
            $accounts = $this->serviceHelper->getService()->management_accounts->listManagementAccounts()->getItems();
            $data = array();

            foreach ($accounts as $account) {
                $data[] = array(
                    'accountId' => $account->getId(),
                    'accountName' => $account->getName()
                );
            }
            return $data;
        }

        /**
         * Get the accountId from the database
         *
         * @return string $accountId
         */
        public function getAccountId($configId=false)
        {
            if (!$this->accountId || $configId) {
                /** @var AnalyticsConfigRepository $analyticsConfigRepository */
                $analyticsConfigRepository = $this->em->getRepository('KunstmaanDashboardBundle:AnalyticsConfig');
                $this->accountId = $analyticsConfigRepository->getConfig($configId)->getAccountId();
            }

            return $this->accountId;
        }

        /**
         * Save the accountId to the database
         */
        public function saveAccountId($accountId, $configId=false)
        {
            $this->accountId = $accountId;
            $this->em->getRepository('KunstmaanDashboardBundle:AnalyticsConfig')->saveAccountId($accountId, $configId);
        }

        /**
         * Check if token is set
         *
         * @return boolean $result
         */
        public function accountIsSet()
        {
            return $this->getAccountId() && '' !== $this->getAccountId();
        }

    /* =============================== PROPERTY =============================== */

        /**
         * Get a list of all available properties
         *
         * @return array $data A list of all properties
         */
        public function getProperties($accountId=false)
        {
            if (!$this->getAccountId() && !$accountId) {
                return false;
            }

            if ($accountId) {
                $webproperties = $this->serviceHelper->getService()->management_webproperties->listManagementWebproperties($accountId);
            } else {
                $webproperties = $this->serviceHelper->getService()->management_webproperties->listManagementWebproperties($this->getAccountId());
            }
            $data = array();

            foreach ($webproperties->getItems() as $property) {
                $data[] = array(
                    'propertyId' => $property->getId(),
                    'propertyName' => $property->getName() . ' (' . $property->getWebsiteUrl() . ')',
                );
            }
            return $data;
        }

        /**
         * Get the propertyId from the database
         *
         * @return string $propertyId
         */
        public function getPropertyId($configId=false)
        {
            if (!$this->propertyId || $configId) {
                /** @var AnalyticsConfigRepository $analyticsConfigRepository */
                $analyticsConfigRepository = $this->em->getRepository('KunstmaanDashboardBundle:AnalyticsConfig');
                $this->propertyId = $analyticsConfigRepository->getConfig($configId)->getPropertyId();
            }

            return $this->propertyId;
        }

        /**
         * Save the propertyId to the database
         */
        public function savePropertyId($propertyId, $configId=false)
        {
            $this->propertyId = $propertyId;
            $this->em->getRepository('KunstmaanDashboardBundle:AnalyticsConfig')->savePropertyId($propertyId, $configId);
        }

        /**
         * Check if propertyId is set
         *
         * @return boolean $result
         */
        public function propertyIsSet()
        {
            return null !== $this->getPropertyId() && '' !== $this->getPropertyId();
        }

    /* =============================== PROFILE =============================== */

        /**
         * Get a list of all available profiles
         *
         * @return array $data A list of all properties
         */
        public function getProfiles($accountId=false, $propertyId=false)
        {
            if (!$this->getAccountId() && !$accountId || !$this->getPropertyId() && !$propertyId) {
                return false;
            }

            // get views
            if ($accountId && $propertyId) {
                $profiles = $this->serviceHelper->getService()->management_profiles->listManagementProfiles(
                    $accountId,
                    $propertyId
                );
            } else {
                $profiles = $this->serviceHelper->getService()->management_profiles->listManagementProfiles(
                    $this->getAccountId(),
                    $this->getPropertyId()
                );
            }

            $result = array();
            foreach ($profiles->getItems() as $profile) {
                $result[] = array(
                        'profileId' => $profile->id,
                        'profileName' => $profile->name,
                        'created' => $profile->created
                    );
            }

            return $result;
        }

        /**
         * Get the propertyId from the database
         *
         * @return string $propertyId
         */
        public function getProfileId($configId=false)
        {
            if (!$this->profileId || $configId) {
                /** @var AnalyticsConfigRepository $analyticsConfigRepository */
                $analyticsConfigRepository = $this->em->getRepository('KunstmaanDashboardBundle:AnalyticsConfig');
                $this->profileId = $analyticsConfigRepository->getConfig($configId)->getProfileId();
            }

            return $this->profileId;
        }

        /**
         * Save the profileId to the database
         */
        public function saveProfileId($profileId, $configId=false)
        {
            $this->profileId = $profileId;
            $this->em->getRepository('KunstmaanDashboardBundle:AnalyticsConfig')->saveProfileId($profileId, $configId);
        }

        /**
         * Check if token is set
         *
         * @return boolean $result
         */
        public function profileIsSet()
        {
            return null !== $this->getProfileId() && '' !== $this->getProfileId();
        }

        /**
         * Get the active profile
         *
         * @return the profile
         */
        public function getActiveProfile() {
            $profiles = $this->getProfiles();
            $profileId = $this->getProfileId();

            foreach ($profiles as $profile) {
                if ($profile['profileId'] == $profileId) {
                    return $profile;
                }
            }
        }

    /* =============================== CONFIG =============================== */

        /**
         * Save the config to the database
         */
        public function saveConfigName($configName, $configId=false)
        {
            $this->em->getRepository('KunstmaanDashboardBundle:AnalyticsConfig')->saveConfigName($configName, $configId);
        }

    /* =============================== AUTH URL =============================== */

        /**
         * get the authUrl
         *
         * @return string $authUrl
         */
        public function getAuthUrl()
        {
            return $this
                ->serviceHelper
                ->getClientHelper()
                ->getClient()
                ->createAuthUrl();
        }


}
