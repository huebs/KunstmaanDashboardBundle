<?php
namespace Kunstmaan\DashboardBundle\Command;

use Doctrine\ORM\EntityManager;
use Kunstmaan\DashboardBundle\Command\Helper\Analytics\ChartDataCommandHelper;
use Kunstmaan\DashboardBundle\Command\Helper\Analytics\GoalCommandHelper;
use Kunstmaan\DashboardBundle\Command\Helper\Analytics\MetricsCommandHelper;
use Kunstmaan\DashboardBundle\Command\Helper\Analytics\UsersCommandHelper;
use Kunstmaan\DashboardBundle\Entity\AnalyticsOverview;
use Kunstmaan\DashboardBundle\Repository\AnalyticsConfigRepository;
use Kunstmaan\DashboardBundle\Repository\AnalyticsOverviewRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class GoogleAnalyticsCommand extends ContainerAwareCommand
{
    /** @var EntityManager $em */
    private $em;

    /** @var OutputInterface $output */
    private $output;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('kuma:dashboard:widget:googleanalytics')
            ->setDescription('Collect the Google Analytics dashboard widget data')
            ->addOption(
                'config',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specify to only update one config',
                false
            )
            ->addOption(
                'segment',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specify to only update one segment',
                false
            );
    }

    /**
     * Inits instance variables for global usage.
     *
     * @param OutputInterface $output The output
     */
    private function init($output)
    {
        $this->output = $output;
        $this->serviceHelper = $this->getContainer()->get('kunstmaan_dashboard.helper.google.analytics.service');
        $this->em = $this->getContainer()->get('doctrine')->getManager();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // init
        $this->init($output);

        // check if token is set
        $configHelper = $this->getContainer()->get('kunstmaan_dashboard.helper.google.analytics.config');
        if (!$configHelper->tokenIsSet()) {
            $this->output->writeln('You haven\'t configured a Google account yet');
            return;
        }

        // get config
        $configId = false;
        try {
            $configId = $input->getOption('config');
        } catch (\Exception $e) {}

        $configRepository = $this->em->getRepository('KunstmaanDashboardBundle:AnalyticsConfig');
        $config = $configRepository->getConfig($configId);

        // add overviews if none exist for this config
        if (!count($config->getOverviews()->toArray())) {
            $configRepository->addOverviews($config);
        }

        // get segment id
        $segmentId = false;
        try {
            $segmentId = $input->getOption('segment');
        } catch (\Exception $e) {}

        // get all overviews (inc. default without a segment) if no segment is specified
        if (!$segmentId) {
            $segments = $config->getSegments();

            foreach ($segments as $segment) {
                $configRepository->initSegment($segment);
            }

            $config = $configRepository->getConfig($configId);
            $overviews = $config->getOverviews();
        } else {
            // only load the overviews of the specified segment
            $segmentRepositry = $this->em->getRepository('KunstmaanDashboardBundle:AnalyticsSegment');
            $segment = $segmentRepositry->getSegment($segmentId);
            $configRepository->initSegment($segment);
            $overviews = $segmentRepositry->getSegment($segmentId)->getOverviews();
        }

        // get the query helper
        $queryHelper = $this->getContainer()->get('kunstmaan_dashboard.helper.google.analytics.query');

        // get data per overview
        foreach ($overviews as $overview) {
            /** @var AnalyticsOverview $overview */
            $this->output->writeln('Getting data for overview "' . $overview->getTitle() . '"');

            // metric data
            $metrics = new MetricsCommandHelper($configHelper, $queryHelper, $this->output, $this->em);
            $metrics->getData($overview);

            if ($overview->getSessions()) { // if there are any visits
                // day-specific data
                $chartData = new ChartDataCommandHelper($configHelper, $queryHelper, $this->output, $this->em);
                $chartData->getData($overview);

                // get goals
                $goals = new GoalCommandHelper($configHelper, $queryHelper, $this->output, $this->em);
                $goals->getData($overview);

                // visitor types
                $visitors = new UsersCommandHelper($configHelper, $queryHelper, $this->output, $this->em);
                $visitors->getData($overview);
            } else {
                // reset overview
                $this->reset($overview);
                $this->output->writeln("\t" . 'No visitors');
            }
            // persist entity back to DB
            $this->output->writeln("\t" . 'Persisting..');
            $this->em->persist($overview);
            $this->em->flush();
        }

        // set new update timestamp
        /** @var AnalyticsConfigRepository $analyticsConfigRepository */
        $analyticsConfigRepository = $this->em->getRepository('KunstmaanDashboardBundle:AnalyticsConfig');
        $analyticsConfigRepository->setUpdated($configId);

        $this->output->writeln('Google Analytics data succesfully updated'); // done
    }

    /**
     * Reset the data for the overview
     *
     * @param AnalyticsOverview $overview The overview
     */
    private function reset(AnalyticsOverview $overview)
    {
        // reset overview
        $overview->setNewUsers(0);
        $overview->setReturningUsers(0);
    }
}
