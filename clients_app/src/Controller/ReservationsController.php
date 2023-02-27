<?php
/**
 * Created by PhpStorm.
 * User: Mateusz
 * Date: 21.11.2018
 * Time: 12:45
 */

namespace App\Controller;


use App\Entity\Miejsca;
use App\Entity\Rezerwacje;
use App\Entity\Seanse;
use App\Entity\Typyrzedow;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReservationsController extends AbstractController
{
    /**
     * @Route("/reservations/{date},{ifAccomplish}/{page<[1-9]\d*>?1}", name="clients_app/reservations", methods={"GET", "POST"})
     */
    public function index(Request $request, $page, $date, $ifAccomplish)
    {
        if ($this->isGranted('ROLE_USER') && AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('clients_app/logout_page');
        }
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($values = $request->request->all()) {
                if ((array_key_exists('accomplish', $values) and array_key_exists('notAccomplish', $values))
                    or (!array_key_exists('accomplish', $values) and !array_key_exists('notAccomplish', $values))) {
                    $ifAccomplish = 2;
                } else {
                    if (array_key_exists('accomplish', $values)) {
                        $ifAccomplish = 1;
                    }
                    if (array_key_exists('notAccomplish', $values)) {
                        $ifAccomplish = 0;
                    }
                }
                if ($values['date'] == null) {
                    $date = 0;
                } else {
                    $date = $values['date'];
                }
                return $this->redirectToRoute('clients_app/reservations', ['date' => $date, 'ifAccomplish' => $ifAccomplish]);
            }

            if ($ifAccomplish === '2') {
                $ifAccomplish = null;
            }
            if ($date === '0') {
                $date = null;
            }

            $values = [
                'ifAccomplish' => $ifAccomplish,
                'date' => $date,
            ];

            $pageLimit = $this->getParameter('page_limit');
            $pageCount = $this->getDoctrine()->getRepository(Rezerwacje::class)->getClientReservationsPageCount(
                $this->getUser(), $pageLimit, $date, $ifAccomplish);

            if ($page > $pageCount and $pageCount != 0)
                return $this->redirectToRoute('clients_app/reservations', $values);
            else {
                $reservations = $this->getDoctrine()->getRepository(Rezerwacje::class)->getClientReservationsPage(
                    $this->getUser(), $page, $pageLimit, $date, $ifAccomplish);
                return $this->render('clientsApp/reservations/list.html.twig', array('reservations' => $reservations, 'values' => $values, 'currentPage' => $page, 'pageCount' => $pageCount));
            }
        } else {
            return $this->redirectToRoute('clients_app/login_page');
        }
    }

    /**
     * @Route("/reservations/add/{id<[1-9]\d*>?}", name="clients_app/reservations/add", methods={"GET", "POST"})
     */
    public function add(Request $request, $id)
    {
        if ($this->isGranted('ROLE_USER') && AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('clients_app/logout_page');
        }

        if (!$seance = $this->getDoctrine()->getRepository(Seanse::class)->find($id) or $seance->getCzyodwolany()) {
            return $this->redirectToRoute('clients_app/movies');
        }


        $submit = $request->request->get('submitNumber');
        $reservation = new Rezerwacje();
        $form = $this->getForm($reservation);
        $form->handleRequest($request);

        $error = null;
        $seatsIdArray = explode(",", $request->get('seatId'));
        $roomLayout = $this->getRoomLayout($this->getDoctrine()->getRepository(Seanse::class)->find($id));
        if (($submit == 0 || $submit == 2) && $form->isSubmitted() && $form->isValid()
            && $this->validSeat($seatsIdArray, $roomLayout) && count($seatsIdArray) <= 10) {
            if ($submit == 2) {
                $seatsArrayCollection = new ArrayCollection();
                foreach ($seatsIdArray as $seatId) {
                    $seatsArrayCollection->add($this->getDoctrine()->getRepository(Miejsca::class)->find($seatId));
                }
                if ($this->isGranted('ROLE_USER')) {
                    $reservation->setCzyodwiedzajacy(0);
                    $reservation->setUzytkownicy($this->getUser());
                } else {
                    $reservation->setCzyodwiedzajacy(1);
                }
                $reservation->setSfinalizowana(0);
                $reservation->setSeanse($seance);
                $reservation->setMiejsca($seatsArrayCollection);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($reservation);
                $entityManager->flush();

                $this->get('session')->set('reservation', $reservation->getId());
                return $this->redirectToRoute('clients_app/reservations/end');

            }
            return $this->render('clientsApp/reservations/summary.html.twig', ['seance' => $seance,
                'rezervationData' => $request->request->all()]);
        } else if ($submit and $submit != 1) {
            $error = 'Coś poszło nie tak';
        }

        if ($this->isGranted('ROLE_USER') and !$submit) {
            $form = $this->getForm($reservation);
            $user = $this->getUser();
            $form->get('imie')->setData($user->getImie());
            $form->get('nazwisko')->setData($user->getNazwisko());
            $form->get('email')->setData($user->getEmail());
            $form->get('telefon')->setData($user->getTelefon());
        }

        $rowType = $this->getDoctrine()->getRepository(Typyrzedow::class)->findAll();
        return $this->render('clientsApp/reservations/add.html.twig', ['seance' => $seance,
            'roomLayout' => $roomLayout, 'checkedSeats' => $seatsIdArray, 'error' => $error,
            'form' => $form->createView(), 'rowType' => $rowType]);
    }

    /**
     * @Route("/reservations/end", name="clients_app/reservations/end", methods={"GET", "POST"})
     */
    public function showEnd(Request $request, \Swift_Mailer $mailer){
        $entityManager = $this->getDoctrine()->getManager();
        $reservation = $entityManager->getRepository(Rezerwacje::class)->find($this->get('session')->get('reservation'));
        $reservationsSeats = $this->getSeatsForReservation($reservation);
        $email = $reservation->getEmail();
        $client = $this->getUser();
        $message = (new \Swift_Message());
        $message->setSubject('Kino SZOK - rezerwacja');
        $message->setFrom('szok.smtp@gmail.com');
        $message->setTo($email);
        $message->setBody($this->renderView(
            'clientsApp/mail/reservationMail.html.twig',
            array('client' => $client, 'reservation' => $reservation, 'selectedSeats' => $reservationsSeats)
        ),
            'text/html'
        );
        $mailer->send($message);
        return $this->render('clientsApp/reservations/end.html.twig', ['email' => $email]);
    }

    private function getSeatsForReservation($reservation)
    {
        if (!$reservation) {
            return false;
        }
        $seats = $this->getDoctrine()->getRepository(Miejsca::class)->getSeatsForReservation($reservation->getId());
        $seance = $reservation->getSeanse();
        $selectedSeats = [];
        foreach ($seats AS $seat) {
            $selectedSeats[$seat->getId()] = array(
                'miejsca' => $seat,
                'status' => 1);
        }
        foreach ($seance->getTranzakcje()->getIterator() AS $transaction) {
            foreach ($transaction->getBilety()->getIterator() AS $ticket) {
                if (!$ticket->getCzyanulowany()) {
                    $transSeat = $ticket->getMiejsca();
                    $seatId = $transSeat->getId();
                    if (array_key_exists($seatId, $selectedSeats)) {
                        $selectedSeats[$seatId]['status'] = 0;
                        $selectedSeats[$seatId]['cena'] = $ticket->getCena();
                        $selectedSeats[$seatId]['rodzajbiletu'] = $ticket->getRodzajebiletow();
                    }
                }
            }
        }
        return $selectedSeats;
    }

    private function getRoomLayout($seance)
    {

        $room = $seance->getSale();
        $roomLayout = array();
        foreach ($room->getRzedy()->getIterator() AS $row) {
            foreach ($row->getMiejsca()->getIterator() AS $seat) {
                $roomLayout[$seat->getId()] = array(
                    'miejsca' => $seat,
                    'status' => $row->getTypyrzedow()->getId());
            }
        }
        foreach ($seance->getTranzakcje()->getIterator() AS $transaction) {
            foreach ($transaction->getBilety()->getIterator() AS $ticket) {
                if (!$ticket->getCzyanulowany()) {
                    $transSeat = $ticket->getMiejsca();
                    if ($roomLayout[$transSeat->getId()]['status'] == 1 or $roomLayout[$transSeat->getId()]['status'] == 2) {
                        $roomLayout[$transSeat->getId()]['status'] = 4;
                    }
                }
            }
        }
        foreach ($seance->getRezerwacje()->getIterator() AS $booking) {
            if (!$booking->isSfinalizowana()) {
                foreach ($booking->getMiejsca()->getIterator() AS $revSeat) {
                    if ($roomLayout[$revSeat->getId()]['status'] == 1 or $roomLayout[$revSeat->getId()]['status'] == 2) {
                        $roomLayout[$revSeat->getId()]['status'] = 3;
                    }
                }
            }
        }

        return $roomLayout;
    }

    private function validSeat($seatsIdArray, $roomLayout)
    {
        foreach ($seatsIdArray as $seatId) {
            if (!preg_match('/^[0-9]{1,}$/', $seatId)) {
                return false;
            }

            if (!array_key_exists($seatId, $roomLayout)) {
                return false;
            }

            if ($roomLayout[$seatId]['status'] != 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param Rezerwacje $reservation
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getForm(Rezerwacje $reservation)
    {
        return $this->createFormBuilder($reservation)
            ->add('imie', TextType::class, array(
                'label' => 'Imię:',
                'attr' => array('class' => 'form-control',
                    "pattern" => "^[A-ZĄĘÓŁŚŻŹĆŃ][a-zA-ZĄĘÓŁŚŻŹĆŃąęółśżźćń ]{2,44}",
                    'title' => 'Polskie znaki, spacja, pierwsza duża litera, od 3 do 45 znaków',
                    'placeholder' => 'Wprowadź imię...',
                    'autocomplete' => "off"),
                'label_attr' => array('class' => "col-sm-2 col-form-label")
            ))
            ->add('nazwisko', TextType::class, array(
                'label' => 'Nazwisko:',
                'attr' => array('class' => 'form-control',
                    "pattern" => "[A-ZĄĘÓŁŚŻŹĆŃ][a-zA-ZĄĘÓŁŚŻŹĆŃąęółśżźćń \-]{2,44}",
                    'title' => 'Polskie znaki, spacja, myślnik, pierwsza duża litera, od 3 do 45 znaków',
                    'placeholder' => 'Wprwadź nazwisko...',
                    'autocomplete' => "off"),
                'label_attr' => array('class' => "col-sm-2 col-form-label")
            ))
            ->add('email', EmailType::class, array(
                'label' => 'E-mail:',
                'attr' => array('class' => 'form-control',
                    "placeholder" => "Wprowdź email...",
                    'autocomplete' => "off"),
                'label_attr' => array('class' => "col-sm-2 col-form-label")
            ))
            ->add('telefon', TextType::class, array(
                'label' => 'Telefon:',
                'attr' => array("class" => "form-control",
                    "pattern" => "[0-9]{9}",
                    "title" => "9 cyfr",
                    "placeholder" => 'Wprowadź numer telefonu...',
                    'autocomplete' => "off"),
                'label_attr' => array('class' => "col-sm-2 col-form-label")
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Zatwierdz',
                'attr' => array('class' => "btn btn-primary float-right",
                    'onsubmit' => "return onsub()")
            ))
            ->getForm();
    }

    /**
     * @Route("/reservations/delete", name="clients_app/reservations/delete", methods={"DELETE"})
     */
    public function delete(Request $request)
    {
        if ($this->isGranted('ROLE_USER') && AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('clients_app/logout_page');
        }

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $reservation = $this->getDoctrine()->getRepository(Rezerwacje::class)->findOneBy(['id' => $request->get('reservationId'), 'uzytkownicy' => $this->getUser()]);
            if (!$reservation or $reservation->isSfinalizowana()) {
                return new JsonResponse(['result' => false]);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($reservation);
            $entityManager->flush();
            return new JsonResponse(['result' => true]);
        }
    }
}