<?php
/**
 * Created by PhpStorm.
 * User: gnowa
 * Date: 28.10.2018
 * Time: 16:56
 */

namespace App\Controller;

use App\Entity\Promocje;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PromotionsController extends Controller
{
    /**
     * @Route("/promotions/{page<[1-9]\d*>?1}", name="workers_app/promotions", methods={"GET"})
     */
    public function index($page)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $pageLimit = $this->getParameter('page_limit');
            $pageCount = $this->getDoctrine()->getRepository(Promocje::class)->getPageCountOfActual($pageLimit);

            if($page > $pageCount and $pageCount != 0)
                return $this->redirectToRoute('workers_app/promotions');
            else {
                $promotions = $this->getDoctrine()->getRepository(Promocje::class)->findActual($page, $pageLimit);
                return $this->render('workersApp/promotions/list.html.twig', array('promotions' => $promotions, 'currentPage' => $page, 'pageCount' => $pageCount));
            }
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/promotions/old/{page<[1-9]\d*>?1}", name="workers_app/promotions/old", methods={"GET"})
     */
    public function old($page)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $pageLimit = $this->getParameter('page_limit');
            $pageCount = $this->getDoctrine()->getRepository(Promocje::class)->getPageCountOfOld($pageLimit);

            if($page > $pageCount and $pageCount != 0)
                return $this->redirectToRoute('workers_app/promotions/old');
            else {
                $promotions = $this->getDoctrine()->getRepository(Promocje::class)->findOld($page, $pageLimit);
                return $this->render('workersApp/promotions/listOld.html.twig', array('promotions' => $promotions, 'currentPage' => $page, 'pageCount' => $pageCount));
            }
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/promotions/add", name="workers_app/promotions/add", methods={"GET", "POST"})
     */
    public function add(Request $request)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN')) {
            $promotion = new Promocje();

            $form = $this->getForm($promotion);

            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()) {
                $promotion = $form->getData();

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($promotion);
                $entityManager->flush();

                return $this->redirectToRoute('workers_app/promotions');
            } else {

                return $this->render('workersApp/promotions/add.html.twig', array('form' => $form->createView()));
            }
        } else if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('workers_app/no_permission');
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/promotions/edit/{id<[1-9]\d*>?}", name="workers_app/promotions/edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, $id)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN')) {
            $promotion = $this->getDoctrine()->getRepository(Promocje::class)->find($id);

            if($promotion == null or $promotion->getPoczatekpromocji()->format("Y-m-d") <= date("Y-m-d"))
                throw new NotFoundHttpException();

            $form = $this->getForm($promotion);

            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()) {
                $promotion = $form->getData();

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($promotion);
                $entityManager->flush();

                return $this->redirectToRoute('workers_app/promotions');
            } else {

                return $this->render('workersApp/promotions/edit.html.twig', array('form' => $form->createView(), 'promotionName', 'promotionName' => $promotion->getNazwa()));
            }
        } else if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('workers_app/no_permission');
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @param Promocje $promotion
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getForm(Promocje $promotion)
    {
        return $this->createFormBuilder($promotion)
            ->add('nazwa', TextType::class, array(
                'label' => 'Nazwa promocji:',
                'label_attr' => array('class' => 'col-sm-2'),
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Wprowadź nazwę...',
                    'pattern' => '[A-Za-z0-9\-ĘÓĄŚŁŻŹĆŃęóąśłżźćń ]{5,45}',
                    'title' => 'Polskie litery, cyfry, spacje i myślniki, od 5 do 45 znaków'
                )
            ))
            ->add('czykwotowa', ChoiceType::class, array(
                'choices' => array('Procentowo' => false, 'Kwotowo' => true),
                'label' => 'Sprosób wyrażenia promocji:',
                'expanded' => true,
                'multiple' => false,
                'choice_attr' => array('class' => 'radio-inline'),
                'label_attr' => array('class' => 'col-sm-2'),
                'attr' => array('class' => 'col-sm-10')
            ))
            ->add('wartosc', NumberType::class, array(
                'label' => 'Wysokość promocji:',
                'label_attr' => array('class' => 'col-sm-2'),
                'scale' => 2,
                'invalid_message' => 'Wartość musi być liczbą',
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => '000.00',
                    'title' => 'Liczba większa od 0, maksymalnie 100, do 2 cyfr po przecinku',
                    'autocomplete' => 'off'
                )
            ))
            ->add('czykobieta', ChoiceType::class, array(
                'choices' => array('Mężczyzna' => false, 'Kobieta' => true),
                'label' => 'Pleć:',
                'expanded' => true,
                'multiple' => false,
                'label_attr' => array('class' => 'col-sm-2')
            ))
            ->add('staz', DateType::class, array(
                'label' => 'Staż:',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'label_attr' => array('class' => 'col-sm-2'),
                'attr' => array(
                    'class' => 'form-control',
                    'title' => 'Data niepóźniejsza niż początek seansu'
                ),
            ))
            ->add('poczatekpromocji', DateType::class, array(
                'label' => 'Początek promocji:',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'label_attr' => array('class' => 'col-sm-2'),
                'attr' => array(
                    'class' => 'form-control',
                    'title' => 'Data nie wcześniejsza niż jutro'
                )
            ))
            ->add('koniecpromocji', DateType::class, array(
                'label' => 'Koniec promocji:',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'label_attr' => array('class' => 'col-sm-2'),
                'attr' => array(
                    'class' => 'form-control',
                    'title' => 'Data niewcześniejsza niż początek promocji'
                )
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Zapisz',
                'attr' => array('class' => 'btn btn-primary')
            ))
            ->getForm();
    }

    /**
     * @Route("/promotions/delete/{id<[1-9]\d*>?}", name="workers_app/promotions/delete", methods={"DELETE"})
     */
    public function delete(Request $request, $id)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN')) {
            $promotion = $this->getDoctrine()->getRepository(Promocje::class)->find($id);
            if($promotion != null and $promotion->getPoczatekpromocji()->format("Y-m-d") > date("Y-m-d")) {
                $entityManager = $this->getDoctrine()->getManager();

                $entityManager->remove($promotion);
                $entityManager->flush();
            }
        }
    }

}