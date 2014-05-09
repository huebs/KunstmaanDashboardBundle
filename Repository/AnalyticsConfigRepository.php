<?php

namespace Kunstmaan\DashboardBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Kunstmaan\DashboardBundle\Entity\AnalyticsConfig;
use Kunstmaan\DashboardBundle\Entity\AnalyticsOverview;

/**
 * AnalyticsConfigRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AnalyticsConfigRepository extends EntityRepository
{
    /**
     * Get the config from the database, creates a new entry if the config doesn't exist yet
     *
     * @return AnalyticsConfig $config
     */
    public function getConfig($id=false)
    {
        $em = $this->getEntityManager();
        $qb = $this->getEntityManager()->createQueryBuilder();
        if ($id) {
            $qb->select('c')
              ->from('KunstmaanDashboardBundle:AnalyticsConfig', 'c')
              ->where('c.id = :id')
              ->setParameter('id', $id);

            $result = $qb->getQuery()->getResult();
            $config = $result[0];
        } else {
            $query = $em->createQuery(
              'SELECT c FROM KunstmaanDashboardBundle:AnalyticsConfig c'
            );

            $result = $query->getResult();

            if (!$result) {
                return $this->createConfig();
            } else {
                $config = $result[0];
            }
        }

        return $config;
    }

    /**
     * Flush a config
     *
     * @param int $id the config id
     */
    public function flushConfig($id=false) {
        $config = $this->getConfig($id);
        $em = $this->getEntityManager();

        foreach ($config->getOverviews() as $overview) {
            $em->remove($overview);
        }
        foreach ($config->getSegments() as $segment) {
            $em->remove($segment);
        }

        $em->flush();
    }

    /**
     * Get a list of all configs
     *
     * @return array
     */
    public function listConfigs() {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c')
          ->from('KunstmaanDashboardBundle:AnalyticsConfig', 'c');
        return $qb->getQuery()->getResult();
    }

    /**
     * Create a new config
     *
     * @return AnalyticsConfig
     */
    public function createConfig() {
        $em = $this->getEntityManager();

        $config = new AnalyticsConfig();
        $em->persist($config);
        $em->flush();

        $this->addOverviews($config);
        return $config;
    }

    /**
     * Initialise a segment by adding new overviews if they don't exist yet
     *
     * @param AnalyticsSegment $segment
     * @param int $configId
     */
    public function initSegment($segment, $configId = false) {
        if (!count($segment->getOverviews()->toArray())) {
            $config = $this->getConfig($configId);
            $this->addOverviews($config, $segment);
        }
    }

    /**
     * Get then default overviews (without a segment)
     *
     * @return array
     */
    public function getDefaultOverviews() {
        $query = $this->getEntityManager()->createQuery(
            'SELECT o FROM KunstmaanDashboardBundle:AnalyticsOverview o WHERE o.segment IS NULL'
        );

        return $query->getResult();
    }

    /**
     * Add overviews for a config and optionally a segment
     *
     * @param AnlyticsConfig $config
     * @param AnalyticsSegment $segment
     */
    public function addOverviews(&$config, &$segment=null) {
        $em = $this->getEntityManager();

        $today = new AnalyticsOverview();
        $today->setTitle('dashboard.ga.tab.today');
        $today->setTimespan(0);
        $today->setStartOffset(0);
        $today->setConfig($config);
        $today->setSegment($segment);
        if ($segment) $segment->getOverviews()[] = $today;
        $config->getOverviews()[] = $today;
        $em->persist($today);

        $yesterday = new AnalyticsOverview();
        $yesterday->setTitle('dashboard.ga.tab.yesterday');
        $yesterday->setTimespan(1);
        $yesterday->setStartOffset(1);
        $yesterday->setConfig($config);
        $yesterday->setSegment($segment);
        if ($segment) $segment->getOverviews()[] = $yesterday;
        $config->getOverviews()[] = $yesterday;
        $em->persist($yesterday);

        $week = new AnalyticsOverview();
        $week->setTitle('dashboard.ga.tab.last_7_days');
        $week->setTimespan(7);
        $week->setStartOffset(1);
        $week->setConfig($config);
        $week->setSegment($segment);
        if ($segment) $segment->getOverviews()[] = $week;
        $config->getOverviews()[] = $week;
        $em->persist($week);

        $month = new AnalyticsOverview();
        $month->setTitle('dashboard.ga.tab.last_30_days');
        $month->setTimespan(30);
        $month->setStartOffset(1);
        $month->setConfig($config);
        $month->setSegment($segment);
        if ($segment) $segment->getOverviews()[] = $month;
        $config->getOverviews()[] = $month;
        $em->persist($month);

        $year = new AnalyticsOverview();
        $year->setTitle('dashboard.ga.tab.last_12_months');
        $year->setTimespan(365);
        $year->setStartOffset(1);
        $year->setConfig($config);
        $year->setSegment($segment);
        if ($segment) $segment->getOverviews()[] = $year;
        $config->getOverviews()[] = $year;
        $em->persist($year);

        $yearToDate = new AnalyticsOverview();
        $yearToDate->setTitle('dashboard.ga.tab.year_to_date');
        $yearToDate->setTimespan(365);
        $yearToDate->setStartOffset(1);
        $yearToDate->setConfig($config);
        $yearToDate->setSegment($segment);
        if ($segment) $segment->getOverviews()[] = $yearToDate;
        $config->getOverviews()[] = $yearToDate;
        $yearToDate->setUseYear(true);
        $em->persist($yearToDate);

        $em->flush();
    }

    /**
     * Update the timestamp when data is collected
     *
     * @param int id
     */
    public function setUpdated($id=false) {
        $em = $this->getEntityManager();
        $config = $this->getConfig($id);
        $config->setLastUpdate(new \DateTime());
        $em->persist($config);
        $em->flush();
    }

    /**
     * saves the token
     *
     * @param string $token
     */
    public function saveToken($token, $id=false) {
        $em    = $this->getEntityManager();
        $config = $this->getConfig($id);
        $config->setToken($token);
        $em->persist($config);
        $em->flush();
    }

    /**
     * saves the property id
     *
     * @param string $propertyId
     */
    public function savePropertyId($propertyId, $id=false) {
        $em    = $this->getEntityManager();
        $config = $this->getConfig($id);
        $config->setPropertyId($propertyId);
        $em->persist($config);
        $em->flush();
    }

    /**
     * saves the account id
     *
     * @param string $accountId
     */
    public function saveAccountId($accountId, $id=false) {
        $em    = $this->getEntityManager();
        $config = $this->getConfig($id);
        $config->setAccountId($accountId);
        $em->persist($config);
        $em->flush();
    }

    /**
     * saves the profile id
     *
     * @param string $profileId
     */
    public function saveProfileId($profileId, $id=false) {
        $em    = $this->getEntityManager();
        $config = $this->getConfig($id);
        $config->setProfileId($profileId);
        $em->persist($config);
        $em->flush();
    }

    /**
     * saves the config name
     *
     * @param string $profileId
     */
    public function saveConfigName($name, $id=false) {
        $em    = $this->getEntityManager();
        $config = $this->getConfig($id);
        $config->setName($name);
        $em->persist($config);
        $em->flush();
    }

    /**
     * Resets the profile id
     *
     * @param int id
     */
    public function resetProfileId($id=false) {
        $em    = $this->getEntityManager();
        $config = $this->getConfig($id);
        $config->setProfileId('');
        $em->persist($config);
        $em->flush();
    }

    /**
     * Resets the  account id, property id and profile id
     *
     * @param int id
     */
    public function resetPropertyId($id=false) {
        $em    = $this->getEntityManager();
        $config = $this->getConfig($id);
        $config->setAccountId('');
        $config->setProfileId('');
        $config->setPropertyId('');
        $em->persist($config);
        $em->flush();
    }
}
