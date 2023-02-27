<?php
/**
 * Created by PhpStorm.
 * User: Piotr
 * Date: 26.11.2018
 * Time: 15:25
 */

namespace App\Controller;

use App\Entity\Sale;
use App\Entity\Seanse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ProgramController extends AbstractController
{
    /**
     * @Route("/program/date",
     *      name="workers_app/program/date")
     */
    public function programDate()
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($_POST) {
                $date = new \DateTime($_POST['date']);
            } else {
                $date = new \DateTime(date("Y-m-d"));
            }
            $seances = $this->getDoctrine()->getRepository(Seanse::class)->getProgram($date);
            return $this->render('workersApp/program/date.html.twig', array(
                'seances' => $seances,
                'date' => $date));

        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/program/rooms",
     *      name="workers_app/program/rooms")
     */
    public function programByRooms()
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($_POST) {
                $date = new \DateTime($_POST['date']);
            } else {
                $date = new \DateTime(date("Y-m-d"));
            }
            $seances = $this->getDoctrine()->getRepository(Seanse::class)->getProgramRooms($date);
            $firstTo = null;
            $first = null;
            $last = null;
            $program = array();
            $prev = null;
            $intervalTo = null;

            if ($seances) {
                foreach ($seances as $key => $value) {
                    if ($first == null || $last == null) {
                        $first = $value->getPoczatekseansu();
                        $last = $value->getSeanceEndTime();
                    } else if ($value->getPoczatekseansu() < $first) {
                        $first = $value->getPoczatekseansu();
                    } else if ($value->getSeanceEndTime() > $last) {
                        $last = $value->getSeanceEndTime();
                    }
                }
                $firstTo = $first->format('H');
                $interval = $first->diff($last);
                $intervalTo = $interval->h + $interval->d*24;

                foreach ($seances as $keyClone => $valueClone) {
                    if ($prev != $valueClone->getSale()->getId()) {
                        $prev = $valueClone->getSale()->getId();
                        $end = $first;
                    }

                    $time = $valueClone->getPoczatekseansu()->diff($valueClone->getSeanceEndTime());
                    $intervalBtw = $end->diff($valueClone->getPoczatekseansu());
                    $program[$keyClone] = array(
                        'interval' => array(
                            'd' => $intervalBtw->d,
                            'h' => $intervalBtw->h,
                            'i' => $intervalBtw->i),
                        'seance' => $valueClone,
                        'time' => $time
                    );
                    $end = $valueClone->getSeanceEndTime();
                }
            }
            return $this->render('workersApp/program/programByRooms.html.twig', array(
                'seances' => $program,
                'date' => $date,
                'first' => $firstTo,
                'interval' => $intervalTo
            ));
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/program/room/{roomId?<[1-9]\d*>}",
     *      name="workers_app/program/room")
     */
    public function programForRoom($roomId)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($_POST) {
                $date = new \DateTime($_POST['date']);
            } else {
                $date = new \DateTime(date("Y-m-d"));
            }
            $room = $this->getDoctrine()->getRepository(Sale::class)->find($roomId);
            if (!$room) {
                return $this->redirectToRoute('workers_app/no_permission');
            } else
                $seances = $this->getDoctrine()->getRepository(Seanse::class)->getProgramForRooms($date, $room);

            return $this->render('workersApp/program/date.html.twig', array(
                'seances' => $seances,
                'date' => $date
            ));

        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }
}