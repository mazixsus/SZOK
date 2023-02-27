<?php

namespace App\Controller;

use App\Entity\Filmy;
use App\Entity\Pracownicy;
use App\Entity\Pulebiletow;
use App\Entity\Rezerwacje;
use App\Entity\Rodzajeplatnosci;
use App\Entity\Sale;
use App\Entity\Seanse;
use App\Entity\SeansMaFilmy;
use App\Entity\Tranzakcje;
use App\Entity\Typyseansow;
use App\Entity\Wydarzeniaspecjalne;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;

class ReportsControler extends AbstractController
{
    /**
     * @Route("/reports/seances/{page<[1-9]\d*>?1},{from<[1-9]\d*>?1},{to<[1-9]\d*>?1}", name="workers_app/reports/seances", methods={"GET", "POST"})
     */
    public function canceledSeances(Request $request, $page, $from, $to)
    {
        if(AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN')) {
            $pageLimit = $this->getParameter('page_limit');

            $year = date("Y");

            $fromDate = new \DateTime();
            $fromDate->setDate($year, '1', '1');
            if($from != 1) {
                $fromDate->setTimestamp($from);
            }
            $fromDate->setTime(0, 0, 0);

            $toDate = new \DateTime();
            $toDate->setDate($year, '12', '31');
            if($to != 1) {
                $toDate->setTimestamp($to);
            }
            $toDate->setTime(23, 59, 59);

            $defaultData = array(
                'from' => $fromDate,
                'to' => $toDate
            );
            $error = NULL;

            $form = $this->getForm($defaultData);

            $form->handleRequest($request);
            if($form->isSubmitted() and $form->isValid()) {
                $data = $form->getData();
                if($data['to'] < $data['from']) {
                    $error = "Data \"do\" nie może być wcześniej niż data \"do\"";
                }
                if($form->isValid() and !$error) {
                    return $this->redirectToRoute('workers_app/reports/seances', array(
                        'from' => $data['from']->getTimestamp(),
                        'to' => $data['to']->getTimestamp()
                    ));
                }
            }

            $seances = $this->getDoctrine()->getRepository(Seanse::class)->getCanceledPage($fromDate, $toDate, $page, $pageLimit);
            $pageCount = $this->getDoctrine()->getRepository(Seanse::class)->getCanceledPageCount($fromDate, $toDate, $pageLimit);
            $seancesCount = $this->getDoctrine()->getRepository(Seanse::class)->getCanceledCount($fromDate, $toDate);

            return $this->render('workersApp/reports/canceledSeances.html.twig', array(
                'form' => $form->createView(),
                'error' => $error,
                'seancesCount' => $seancesCount,
                'seances' => $seances,
                'currentPage' => $page,
                'pageCount' => $pageCount,
                'pathName' => 'workers_app/reports/seances',
                'pathParams' => array(
                    'from' => $fromDate->getTimestamp(),
                    'to' => $toDate->getTimestamp()
                )
            ));

        } else if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('workers_app/no_permission');
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/reports/sale", name="workers_app/reports/sale", methods={"GET", "POST"})
     */
    public function saleForm(Request $request)
    {
        if(AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN')) {
            $fromDate = new \DateTime();
            $fromDate->setTime(0, 0, 0);
            $year = $fromDate->format("Y");
            $month = $fromDate->format("m");
            $day = NULL;
            switch($month) {
                case "4":
                case "6":
                case "9":
                case "11":
                    $day = 30;
                    break;
                case "2":
                    {
                        if($year % 400 == 0 or ($year % 4 == 0 and $year % 100 != 0)) {
                            $day = 29;
                        } else {
                            $day = 28;
                        }
                    };
                    break;
                default:
                    $day = 31;
            }

            $fromDate->setDate($year, $month, 1);

            $toDate = clone $fromDate;
            $toDate->setDate($year, $month, $day);

            $defaultData = array(
                'from' => $fromDate,
                'to' => $toDate,
                'where' => array(),
                'employee' => NULL,
                'payment' => array(),
                'movie' => NULL
            );
            $error = NULL;

            $form = $this->getSaleForm($defaultData);

            $form->handleRequest($request);

            if($form->isSubmitted() and $form->isValid()) {
                $data = $form->getData();
                if($data['movie']) $data['movie'] = $this->getDoctrine()->getRepository(Filmy::class)->find($data['movie']);
                if($data['to'] < $data['from']) {
                    $error = "Data \"do\" nie może być wcześniej niż data \"do\"";
                }

                if(!$error) {
                    $transactions = $this->getDoctrine()->getRepository(Tranzakcje::class)->getSalesReport($data);

                    return $this->render('workersApp/reports/saleView.html.twig', array(
                        'data' => $data,
                        'transactions' => $transactions
                    ));
                }
            }

            return $this->render('workersApp/reports/saleForm.html.twig', array(
                'form' => $form->createView(),
                'error' => $error
            ));

        } else if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('workers_app/no_permission');
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @param array $defaultData
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getForm(array $defaultData)
    {
        return $this->createFormBuilder($defaultData)
            ->add('from', DateType::class, array(
                'label' => 'Od:',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'invalid_message' => 'Wprowadzona wartość nie jest datą.',
                'attr' => array(
                    'style' => 'width: 11rem',
                    'class' => 'form-control mx-2'
                )
            ))
            ->add('to', DateType::class, array(
                'label' => 'Do:',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'invalid_message' => 'Wprowadzona wartość nie jest datą.',
                'attr' => array(
                    'style' => 'width: 11rem',
                    'class' => 'form-control mx-2',
                    'title' => 'Data niewcześniejsza niż początek ważności'
                )
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Pokaż',
                'attr' => array('class' => 'btn btn-primary')
            ))
            ->getForm();
    }

    /**
     * @param array $defaultData
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getSaleForm(array $defaultData)
    {
        $movies = $this->getDoctrine()->getRepository(Filmy::class)->findBy(array(),array('datapremiery' => 'DESC'));
        $array = array();
        foreach($movies AS $movie){
            $array[$movie->__toString()] = $movie->getId();
        }

        return $this->createFormBuilder($defaultData)
            ->add('from', DateType::class, array(
                'label' => 'Od:',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'invalid_message' => 'Wprowadzona wartość nie jest datą.',
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('to', DateType::class, array(
                'label' => 'Do:',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'invalid_message' => 'Wprowadzona wartość nie jest datą.',
                'attr' => array(
                    'class' => 'form-control',
                    'title' => 'Data niewcześniejsza niż data \'Od\''
                )
            ))
            ->add('where', ChoiceType::class, array(
                'choices' => array('Przy kasie' => 'AP', 'Przez Internet' => 'AK'),
                'label' => 'Miejsce sprzedaży:',
                'expanded' => true,
                'multiple' => true
            ))
            ->add('employee', EntityType::class, array(
                'class' => Pracownicy::class,
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->andWhere('p.czyaktywny = 0 OR p.czyaktywny IS NULL')
                        ->orderBy('CONCAT_WS(p.imie,\' \',p.nazwisko)');
                },
                'label' => 'Pracownik:',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'Nie dotyczy',
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('payment', EntityType::class, array(
                'class' => Rodzajeplatnosci::class,
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->andWhere('d.usunieto = 0 OR d.usunieto IS NULL');
                },
                'label' => 'Rodzaj płatności:',
                'expanded' => true,
                'multiple' => true
            ))
            ->add('movie', ChoiceType::class, array(
                'choices' => $array,
                'label' => 'Filmy:',
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Nie dotyczy',
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Generuj',
                'attr' => array('class' => 'btn btn-primary pull-right')
            ))
            ->getForm();
    }

}
