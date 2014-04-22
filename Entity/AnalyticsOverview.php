<?php

namespace Kunstmaan\DashboardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AnalyticsOverview
 *
 * @ORM\Table(name="kuma_analytics_overview")
 * @ORM\Entity(repositoryClass="Kunstmaan\DashboardBundle\Repository\AnalyticsOverviewRepository")
 */
class AnalyticsOverview extends \Kunstmaan\AdminBundle\Entity\AbstractEntity
{

    /**
     * Get percentage of returning users
     *
     * @return int
     */
    public function getReturningUsersPercentage() {
        return $this->returningUsers ? round(($this->returningUsers / $this->sessions) * 100) : 0;
    }

    /**
     * Get percentage of new users
     *
     * @return int
     */
    public function getNewUsersPercentage() {
        return $this->newUsers ? round(($this->newUsers / $this->sessions) * 100) : 0;
    }

    /**
     * @ORM\OneToMany(targetEntity="AnalyticsGoal", mappedBy="overview", cascade={"persist"})
     */
    private $goals;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var integer
     *
     * @ORM\Column(name="timespan", type="integer")
     */
    private $timespan;

    /**
     * @var integer
     *
     * @ORM\Column(name="start_days_ago", type="integer")
     */
    private $startOffset = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="use_year", type="boolean")
     */
    private $useYear = false;

    /**
     * @var integer
     *
     * @ORM\Column(name="sessions", type="integer")
     */
    private $sessions = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="users", type="integer")
     */
    private $users = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="returning_users", type="integer")
     */
    private $returningUsers = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="new_users", type="integer")
     */
    private $newUsers = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="pageviews", type="integer")
     */
    private $pageviews = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="pages_per_session", type="integer")
     */
    private $pagesPerSession = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="avg_session_duration", type="integer")
     */
    private $avgSessionDuration = 0;






    /**
     * Set goals
     *
     * @param array $goals
     * @return AnalyticsDailyOverview
     */
    public function setGoals($goals)
    {
        $this->goals = $goals;

        return $this;
    }

    /**
     * Get goals
     *
     * @return array
     */
    public function getGoals()
    {
        return $this->goals;
    }

    public function getActiveGoals() {
        $goals = array();
        foreach ($this->goals->toArray() as $goal) {
            if ($goal->getVisits()) $goals[] = $goal;
        }
        return $goals;
    }

    /**
     * @var array
     *
     * @ORM\Column(name="chart_data", type="text")
     */
    private $chartData = '';


    /**
     * Set chartData
     *
     * @param array $chartData
     * @return AnalyticsDailyOverview
     */
    public function setChartData($chartData)
    {
        $this->chartData = $chartData;

        return $this;
    }

    /**
     * Get chartData
     *
     * @return array
     */
    public function getChartData()
    {
        return $this->chartData;
    }

    /**
     * Set newUsers
     *
     * @param string $newUsers
     * @return AnalyticsOverview
     */
    public function setNewUsers($newUsers)
    {
        $this->newUsers = $newUsers;

        return $this;
    }

    /**
     * Get newUsers
     *
     * @return string
     */
    public function getNewUsers()
    {
        return $this->newUsers;
    }


    /**
     * Set returningUsers
     *
     * @param string $returningUsers
     * @return AnalyticsOverview
     */
    public function setReturningUsers($returningUsers)
    {
        $this->returningUsers = $returningUsers;

        return $this;
    }

    /**
     * Get returningUsers
     *
     * @return string
     */
    public function getReturningUsers()
    {
        return $this->returningUsers;
    }


    /**
     * Set title
     *
     * @param string $title
     * @return AnalyticsOverview
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set startOffset
     *
     * @param integer $startOffset
     * @return AnalyticsOverview
     */
    public function setStartOffset($startOffset)
    {
        $this->startOffset = $startOffset;

        return $this;
    }

    /**
     * Get startOffset
     *
     * @return integer
     */
    public function getStartOffset()
    {
        return $this->startOffset;
    }

    /**
     * Set timespan
     *
     * @param integer $timespan
     * @return AnalyticsOverview
     */
    public function setTimespan($timespan)
    {
        $this->timespan = $timespan;

        return $this;
    }

    /**
     * Get timespan
     *
     * @return integer
     */
    public function getTimespan()
    {
        return $this->timespan;
    }

    /**
     * Set sessions
     *
     * @param integer $sessions
     * @return AnalyticsOverview
     */
    public function setSessions($sessions)
    {
        $this->sessions = $sessions;

        return $this;
    }

    /**
     * Get sessions
     *
     * @return integer
     */
    public function getSessions()
    {
        return $this->sessions;
    }

    /**
     * Get Users
     *
     * @return integer
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set users
     *
     * @param integer $users
     * @return AnalyticsOverview
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Set pageviews
     *
     * @param integer $pageviews
     * @return AnalyticsOverview
     */
    public function setPageviews($pageviews)
    {
        $this->pageviews = $pageviews;

        return $this;
    }

    /**
     * Get pageviews
     *
     * @return integer
     */
    public function getPageviews()
    {
        return $this->pageviews;
    }

    /**
     * Set pagesPerSession
     *
     * @param integer $pagesPerSession
     * @return AnalyticsOverview
     */
    public function setPagesPerSession($pagesPerSession)
    {
        $this->pagesPerSession = $pagesPerSession;

        return $this;
    }

    /**
     * Get pagesPerSession
     *
     * @return integer
     */
    public function getPagesPerSession()
    {
        return $this->pagesPerSession;
    }

    /**
     * Set avgSessionDuration
     *
     * @param integer $avgSessionDuration
     * @return AnalyticsOverview
     */
    public function setAvgSessionDuration($avgSessionDuration)
    {
        $this->avgSessionDuration = $avgSessionDuration;

        return $this;
    }

    /**
     * Get avgSessionDuration
     *
     * @return integer
     */
    public function getAvgSessionDuration()
    {
        return $this->avgSessionDuration;
    }

    /**
     * Set useYear
     *
     * @param integer $useYear
     * @return AnalyticsOverview
     */
    public function setUseYear($useYear)
    {
        $this->useYear = $useYear;

        return $this;
    }

    /**
     * Get useYear
     *
     * @return integer
     */
    public function getUseYear()
    {
        return $this->useYear;
    }
}
