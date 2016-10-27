<?php

/*
 * This file is part of the Kimai package.
 *
 * (c) Kevin Papst <kevin@kevinpapst.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TimesheetBundle\DataFixtures\ORM;

use AppBundle\Entity\User;
use TimesheetBundle\Entity\Activity;
use TimesheetBundle\Entity\Project;
use TimesheetBundle\Entity\Timesheet;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\DataFixtures\ORM\LoadFixtures as AppBundleLoadFixtures;

/**
 * Defines the sample data to load in the database when running the unit and
 * functional tests. Execute this command to load the data:
 *
 *   $ php bin/console doctrine:fixtures:load
 *
 * @author Kevin Papst <kevin@kevinpapst.de>
 */
class LoadFixtures extends AppBundleLoadFixtures
{
    const AMOUNT_ACTIVITIES = 10;       // maximum activites per project
    const AMOUNT_TIMESHEET = 1000;      // timesheet entries total
    const AMOUNT_PROJECTS = 20;         // projects entries total
    const AMOUNT_CUSTOMER = 10;         // customer entries total

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadProjects($manager);
        $this->loadActivities($manager);
        $this->loadTimesheet($manager);
    }

    /**
     * @param ObjectManager $manager
     * @return User[]
     */
    protected function getAllUsers(ObjectManager $manager)
    {
        $all = [];
        /* @var User[] $entries */
        $entries = $manager->getRepository(User::class)->findAll();
        foreach ($entries as $temp) {
            $all[$temp->getId()] = $temp;
        }
        return $all;
    }

    /**
     * @param ObjectManager $manager
     * @return Project[]
     */
    protected function getAllProjects(ObjectManager $manager)
    {
        $all = [];
        /* @var Project[] $entries */
        $entries = $manager->getRepository(Project::class)->findAll();
        foreach ($entries as $temp) {
            $all[$temp->getId()] = $temp;
        }
        return $all;
    }
    /**
     * @param ObjectManager $manager
     * @return Activity[]
     */
    protected function getAllActivities(ObjectManager $manager)
    {
        $all = [];
        /* @var Activity[] $entries */
        $entries = $manager->getRepository(Activity::class)->findAll();
        foreach ($entries as $temp) {
            $all[$temp->getId()] = $temp;
        }
        return $all;
    }

    private function loadTimesheet(ObjectManager $manager)
    {
        $allUser = $this->getAllUsers($manager);
        $amountUser = count($allUser);

        $allActivity = $this->getAllActivities($manager);
        $amountActivity = count($allActivity);

        for ($i = 0; $i <= self::AMOUNT_TIMESHEET; $i++) {

            $start = new \DateTime();
            $start = $start->modify('- ' . (rand(1, 400)) . ' days');
            $start = $start->modify('- ' . (rand(1, 86400)) . ' seconds');
            $end = clone $start;
            $end = $end->modify('+ '.(rand(1, 43200)).' seconds');

            $entry = new Timesheet();
            $entry->setActivity($allActivity[array_rand($allActivity)]);
            $entry->setStatusid(1);                                         // TODO
            $entry->setBillable($i % 2 == 0);
            $entry->setBudget(0);
            $entry->setCleared($i % 7 == 0);
            $entry->setComment($this->getRandomPhrase());
            $entry->setDescription($this->getRandomPhrase());
            $entry->setLocation($this->getRandomLocation());
            $entry->setStart($start->getTimestamp());
            $entry->setEnd($end->getTimestamp());
            $entry->setDuration($end->modify('- ' . $start->getTimestamp() . ' seconds')->getTimestamp());
            $entry->setUser($allUser[rand(1, $amountUser)]);
            //$entry->setApproved(false);                                   // TODO
            //$entry->setFixedrate();                                       // TODO
            //$entry->setRate();                                            // TODO
            //$entry->setTrackingnumber();                                  // TODO

            $manager->persist($entry);
        }
        $manager->flush();
    }

    private function loadProjects(ObjectManager $manager)
    {
        for ($i = 0; $i <= self::AMOUNT_PROJECTS; $i++) {

            $entry = new Project();
            $entry->setName($this->getRandomProject());
            $entry->setBudget(rand(1000, 100000));
            $entry->setComment($this->getRandomPhrase());
            $entry->setCustomerId(rand(1, self::AMOUNT_CUSTOMER));          // TODO
            $entry->setVisible($i % 3 != 0);

            $manager->persist($entry);
        }
        $manager->flush();
    }

    private function loadActivities(ObjectManager $manager)
    {
        $allProject = $this->getAllProjects($manager);

        foreach ($allProject as $projectId => $project) {
            $activityCount = rand(1, self::AMOUNT_ACTIVITIES);
            for ($i = 0; $i < $activityCount; $i++) {
                $entry = new Activity();
                $entry->setProject($project);
                $entry->setName($this->getRandomActivity());
                $entry->setComment($this->getRandomPhrase());
                $entry->setVisible($i % 3 != 0);

                $manager->persist($entry);
            }
        }
        $manager->flush();
    }

    private function getActivities()
    {
        return [
            'Design',
            'Programming',
            'Testing',
            'Documentation',
            'Pause',
            'Internal',
            'Research',
            'Meeting',
        ];
    }

    private function getRandomActivity()
    {
        $all = $this->getActivities();
        return $all[array_rand($all)];
    }

    private function getProjects()
    {
        return [
            'FooBar',
            'Relaunch',
            'Refactoring',
            'Test Automatisation',
            'Website redesign',
            'Services',
        ];
    }

    private function getRandomProject()
    {
        $all = $this->getProjects();
        return $all[array_rand($all)];
    }

    private function getLocations()
    {
        return [
            'Köln',
            'München',
            'New York',
            'Buenos Aires',
            'Hawai',
            'Amsterdam',
            'London',
            'San Francisco',
            'Tokio',
            'Berlin',
            'Sao Paulo',
            'Mexico City',
        ];
    }

    private function getRandomLocation()
    {
        $all = $this->getLocations();
        return $all[array_rand($all)];
    }
}