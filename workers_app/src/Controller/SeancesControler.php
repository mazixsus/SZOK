<?php

namespace App\Controller;

use App\Entity\Filmy;
use App\Entity\Pulebiletow;
use App\Entity\Rezerwacje;
use App\Entity\Sale;
use App\Entity\Seanse;
use App\Entity\SeansMaFilmy;
use App\Entity\Typyseansow;
use App\Entity\Wydarzeniaspecjalne;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;

class SeancesControler extends AbstractController
{
    /**
     * @Route("/seances/new", name="workers_app/seances/new", methods={"GET","POST"})
     */
    public function new(Request $request)
    {
        if(AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN')) {
            $seance = new Seanse();
            $collectionValues = '';

            $form = $this->getForm($seance);

            $smfError = NULL;
            $wsError = NULL;
            $psError = NULL;
            $psError2 = NULL;

            $ws = NULL;

            $form->handleRequest($request);

            $filmy = array();

            if($form->isSubmitted()) {
                $collectionValues = $request->get('form_collectionValues');
                if(array_key_exists('seansMaFilmy', $request->get('form'))) {
                    $filmy_id = $request->get('form')['seansMaFilmy'];
                    if(count($filmy_id) != count(array_unique($filmy_id))) {
                        $smfError = "Nie możesz umieścić 2 takich samych filmów w wydarzeniu specjalnym.";
                    } else if(count($filmy_id) > 6) {
                        $smfError = "Wydarzenie specjalne nie może mieć więcej niż 6 filmów.";
                    } else {
                        foreach($filmy_id as $value) {
                            if(!$film = $this->getDoctrine()->getRepository(Filmy::class)->find($value)) {
                                $smfError = "Jeden z wprowadzonych filmów nie istnieje";
                                break;
                            } else {
                                array_push($filmy, $film);
                            }
                        }

                        if(array_key_exists('poczatekseansu', $request->get('form'))) {
                            $ps = $request->get('form')['poczatekseansu']['date'];
                            foreach($filmy AS $film) {
                                if($film->getDataPremiery()->format("Y-m-d") > $ps) {
                                    $psError = "Seans nie może się rozpoczynać wcześniej niż w dniu premiery filmu.";
                                }
                            }
                        }
                        if(count($filmy) > 1) {
                            if(!array_key_exists('wydarzeniaspecjalne', $request->get('form'))) {
                                $wsError = "Wskazanie rodzaju wydarzenia jest wymagane, gdy wybrano więcej niż jeden film.";
                            } else {
                                $ws = $request->get('form')['wydarzeniaspecjalne'];
                            }
                        }
                        $collectionValues = implode($filmy_id, '/');

                        $seanceStartDate = new \DateTime($_POST['form']['poczatekseansu']['date'] . ' ' . $_POST['form']['poczatekseansu']['time']['hour'] . ':' . $_POST['form']['poczatekseansu']['time']['minute']);

                        $seanceEndDate = $this->getEndTime($seanceStartDate,$collectionValues);
                        $room = $this->getDoctrine()->getRepository(\App\Entity\Sale::class)->find($_POST['form']['sale']);
                        if($qSeance = $this->getDoctrine()->getRepository(Seanse::class)->endTimeIsInvalid($seanceStartDate, $seanceEndDate, $room))
                        {
                            $psError2 = "W sali " . $room->getNumersali() . " zaplanowany jest już seans w godzinach "
                                . $qSeance->getPoczatekseansu()->format("H:i") . " - " . $qSeance->getSeanceEndTime()->format("H:i")
                                . ". Obecnie konfigurowany seans odbywałby się w godzinach "
                                . $seanceStartDate->format("H:i")  . " - " . $seanceEndDate->format("H:i")
                                . ".";
                        }
                    }
                } else {
                    $smfError = "Należy wybrać przynajmniej jeden film.";
                }
                if($form->isValid() and !$smfError and !$wsError and !$psError and !$psError2 and count($filmy)) {
                    $seance = $form->getData();

                    $seance->setSeansMaFilmy(new ArrayCollection());
                    if($ws) $seance->setWydarzeniaspecjalne($this->getDoctrine()->getRepository(Wydarzeniaspecjalne::class)->find($ws));
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($seance);
                    $entityManager->flush();

                    foreach($filmy AS $key => $film) {
                        $seansMaFilmy = new SeansMaFilmy();

                        $seansMaFilmy->setFilmy($film);
                        $seansMaFilmy->setSeanse($seance);
                        $seansMaFilmy->setKolejnosc($key + 1);

                        $entityManager->persist($seansMaFilmy);
                        $entityManager->flush();
                    }

                    return $this->redirectToRoute('workers_app/seances/show', array('id' => $seance->getId()));
                }
            }

            return $this->render('workersApp/seances/new.html.twig', array(
                'form' => $form->createView(),
                'collectionValues' => $collectionValues,
                'smfError' => $smfError,
                'wsError' => $wsError,
                'psError' => $psError,
                'psError2' => $psError2
            ));
        } else if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('workers_app/no_permission');
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/seances/edit/{id<[1-9]\d*>?}", name="workers_app/seances/edit", methods={"GET","POST"})
     */
    public function edit(Request $request, $id)
    {
        if(AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN')) {
            $seance = $this->getDoctrine()->getRepository(Seanse::class)->find($id);

            if(!$seance or $seance->getCzyodwolany() or $seance->getRezerwacje()->count() or $seance->getTranzakcje()->count())
                return $this->redirectToRoute('workers_app/no_permission');

            $collectionValues = $seance->getInitialCollectionsValues();
            $smfsToRemove = $seance->getSeansMaFilmy()->getValues();

            $form = $this->getForm($seance);

            $smfError = NULL;
            $wsError = NULL;
            $psError = NULL;
            $psError2 = NULL;

            $ws = NULL;

            $form->handleRequest($request);

            $filmy = array();

            if($form->isSubmitted()) {
                $collectionValues = $request->get('form_collectionValues');
                if(array_key_exists('seansMaFilmy', $request->get('form'))) {
                    $filmy_id = $request->get('form')['seansMaFilmy'];
                    if(count($filmy_id) != count(array_unique($filmy_id))) {
                        $smfError = "Nie możesz umieścić 2 takich samych filmów w wydarzeniu specjalnym.";
                    } else if(count($filmy_id) > 6) {
                        $smfError = "Wydarzenie specjalne nie może mieć więcej niż 6 filmów.";
                    } else {
                        foreach($filmy_id as $value) {
                            if(!$film = $this->getDoctrine()->getRepository(Filmy::class)->find($value)) {
                                $smfError = "Jeden z wprowadzonych filmów nie istnieje";
                                break;
                            } else {
                                array_push($filmy, $film);
                            }
                        }

                        if(array_key_exists('poczatekseansu', $request->get('form'))) {
                            $ps = $request->get('form')['poczatekseansu']['date'];
                            foreach($filmy AS $film) {
                                if($film->getDataPremiery()->format("Y-m-d") > $ps) {
                                    $psError = "Seans nie może się rozpoczynać wcześniej niż w dniu premiery filmu.";
                                }
                            }
                        }
                        if(count($filmy) > 1) {
                            if(!array_key_exists('wydarzeniaspecjalne', $request->get('form'))) {
                                $wsError = "Wskazanie rodzaju wydarzenia jest wymagane, gdy wybrano więcej niż jeden film.";
                            } else {
                                $ws = $request->get('form')['wydarzeniaspecjalne'];
                                $request->request->add(array('form[wydarzeniaspecjalne]', $ws));
                            }
                        }
                        $collectionValues = implode($filmy_id, '/');

                        $seanceStartDate = new \DateTime($_POST['form']['poczatekseansu']['date'] . ' ' . $_POST['form']['poczatekseansu']['time']['hour'] . ':' . $_POST['form']['poczatekseansu']['time']['minute']);
                        $seanceEndDate = $this->getEndTime($seanceStartDate,$collectionValues);
                        $room = $this->getDoctrine()->getRepository(\App\Entity\Sale::class)->find($_POST['form']['sale']);

                        if($qSeance = $this->getDoctrine()->getRepository(Seanse::class)->endTimeIsInvalid($seanceStartDate, $seanceEndDate, $room, $id))
                        {
                            $psError2 = "W sali " . $room->getNumersali() . " zaplanowany jest już seans w godzinach "
                                . $qSeance->getPoczatekseansu()->format("H:i") . " - " . $qSeance->getSeanceEndTime()->format("H:i")
                                . ". Obecnie konfigurowany seans odbywałby się w godzinach "
                                . $seanceStartDate->format("H:i")  . " - " . $seanceEndDate->format("H:i")
                                . ".";
                        }
                    }
                } else {
                    $smfError = "Należy wybrać przynajmniej jeden film.";
                }

                if($form->isValid() and !$smfError and !$wsError and !$psError and !$psError2 and count($filmy)) {
                    $seance = $form->getData();
                    $seance->setSeansMaFilmy(new ArrayCollection());
                    if(count($filmy) == 1)
                        $seance->setWydarzeniaspecjalne(NULL);
                    $entityManager = $this->getDoctrine()->getManager();

                    foreach($smfsToRemove as $smf) {
                        $smfToRemove = $this->getDoctrine()->getRepository(SeansMaFilmy::class)->find($smf->getId());
                        $entityManager->remove($smfToRemove);
                        $entityManager->flush();
                    }

                    if($ws) $seance->setWydarzeniaspecjalne($this->getDoctrine()->getRepository(Wydarzeniaspecjalne::class)->find($ws));
                    $entityManager->merge($seance);
                    $entityManager->flush();

                    foreach($filmy AS $key => $film) {
                        $seansMaFilmy = new SeansMaFilmy();

                        $seansMaFilmy->setFilmy($film);
                        $seansMaFilmy->setSeanse($seance);
                        $seansMaFilmy->setKolejnosc($key + 1);

                        $entityManager->persist($seansMaFilmy);
                        $entityManager->flush();
                    }

                    return $this->redirectToRoute('workers_app/seances/show', array('id' => $seance->getId()));
                }
            }

            return $this->render('workersApp/seances/edit.html.twig', array(
                'form' => $form->createView(),
                'collectionValues' => $collectionValues,
                'smfError' => $smfError,
                'wsError' => $wsError,
                'psError' => $psError,
                'seance' => $seance,
                'psError2' => $psError2
            ));
        } else if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('workers_app/no_permission');
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/seances/show/{id<[1-9]\d*>?}", name="workers_app/seances/show", methods={"GET"})
     */
    public function show($id)
    {
        if(AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN')) {
            $seance = $this->getDoctrine()->getRepository(Seanse::class)->find($id);
            if(!$seance or $seance->getCzyodwolany() )
                return $this->redirectToRoute('workers_app/no_permission');

            $booking = $this->getDoctrine()->getRepository(Rezerwacje::class)->findBookingNotFinalized($seance);

            return $this->render('workersApp/seances/show.html.twig', array(
                'seance' => $seance,
                'booking' => $booking
            ));

        } else if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('workers_app/no_permission');
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/seances/rmRev/{id<[1-9]\d*>?}", name="workers_app/seances/rmRev", methods={"DELETE"})
     */
    public function removeReservations($id)
    {
        if(($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN'))
            and $seance = $this->getDoctrine()->getRepository(Seanse::class)->find($id)
            and $bookings = $this->getDoctrine()->getRepository(Rezerwacje::class)->findBookingNotFinalized($seance)
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            foreach($bookings as $booking) {
                $entityManager->remove($booking);
                $entityManager->flush();
            }
        }
    }

    /**
     * @Route("/seances/rmS/{id<[1-9]\d*>?}", name="workers_app/seances/rmSeance", methods={"DELETE"})
     */
    public function removeSeance($id)
    {
        if(($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN'))
            and $seance = $this->getDoctrine()->getRepository(Seanse::class)->find($id)
            and $seance->getRezerwacje()->isEmpty()
            and $seance->getTranzakcje()->isEmpty()
            and !$seance->getCzyodwolany()
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $seance->setCzyodwolany(true);
            $entityManager->merge($seance);
            $entityManager->flush();
        }
    }

    /**
     * @param $seance
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getForm($seance)
    {
        return $this->createFormBuilder($seance)
            ->add('poczatekseansu', DateTimeType::class, array(
                'date_format' => 'yyyy-MM-dd',
                'date_widget' => 'single_text',
                'placeholder' => array(
                    'hour' => '--',
                    'minute' => '--'
                ),
                'minutes' => range(0,55,5),
                'label' => false
            ))
            ->add('sale', EntityType::class, array(
                'class' => Sale::class,
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.numersali', 'ASC');
                },
                'label' => 'Sala:',
                'expanded' => false,
                'multiple' => false,
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('typyseansow', EntityType::class, array(
                'class' => Typyseansow::class,
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->andWhere('d.usunieto = 0 OR d.usunieto IS NULL');
                },
                'label' => 'Format:',
                'expanded' => false,
                'multiple' => false,
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('pulebiletow', EntityType::class, array(
                'class' => Pulebiletow::class,
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->andWhere('d.usunieto = 0 OR d.usunieto IS NULL');
                },
                'label' => 'Pula biletów:',
                'expanded' => false,
                'multiple' => false,
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('wydarzeniaspecjalne', EntityType::class, array(
                'class' => Wydarzeniaspecjalne::class,
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->andWhere('d.usunieto = 0 OR d.usunieto IS NULL');
                },
                'label' => 'Wydarzenie specjalne:',
                'placeholder' => 'Wybierz rodzaj wydarzenia...',
                'expanded' => false,
                'multiple' => false,
                'disabled' => true,
                'required' => false,
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('seansMaFilmy', CollectionType::class, array(
                'label' => 'Filmy:',
                'entry_type' => EntityType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_options' => array(
                    'class' => Filmy::class,
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                        return $er->createQueryBuilder('f')
                            ->orderBy('f.datapremiery', 'DESC');
                    },
                    'label' => '__display_name__',
                    'expanded' => false,
                    'multiple' => false,
                    'attr' => array(
                        'class' => 'form-control',
                        'required' => 'required'
                    )
                )
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Zapisz',
                'attr' => array('class' => 'btn btn-primary pull-right')
            ))
            ->getForm();
    }

    private function getEndTime(\DateTime $seanceStartDate, string $collectionValues)
    {
        $beginning = clone $seanceStartDate;
        $collectionArray = explode('/', $collectionValues);
        foreach($collectionArray AS $movieId) {
            $movie = $this->getDoctrine()->getRepository(Filmy::class)->find($movieId);
            $beginning->add(new \DateInterval('PT' . ($movie->getCzastrwania() + $movie->getCzasreklam()) . 'M'));
        }
        return $beginning;
    }
}
