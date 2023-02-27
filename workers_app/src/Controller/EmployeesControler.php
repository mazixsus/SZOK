<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\Pracownicy;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class EmployeesControler extends AbstractController
{
    /**
     * @Route("/employees/new", name="workers_app/employees/new")
     */
    public function addEmployee(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            $pracownik = new Pracownicy();
            $form = $this->createFormBuilder($pracownik)
                ->add('role', EntityType::class, array(
                    'class' => Role::class,
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                        return $er->createQueryBuilder('d')
                            ->andWhere('d.usunieto = 0 OR d.usunieto IS NULL');
                    },
                    'label' => "Rola: ",
                    'expanded' => false,
                    'multiple' => false,
                    'attr' => array("class" => "form-control"),
                    'label_attr' => array('class' => "col-sm-2 col-form-label")
                ))
                ->add('login', TextType::class, array(
                    'label' => 'Login:',
                    'attr' => array('class' => 'form-control',
                        "pattern" => "[A-Za-z0-9\-_]{5,45}",
                        "placeholder" => "Wprowadź login...",
                        'title' => 'Polskie litery, cyfry, myślniki, podkreślenia, od 5 do 45 znaków.',
                        'autocomplete' => "off"),
                    'label_attr' => array('class' => "col-sm-2 col-form-label")
                ))
                ->add('haslo', TextType::class, array(
                    'label' => 'Hasło:',
                    'attr' => array('class' => 'form-control',
                        "pattern" => "\S{8,64}",
                        'title' => 'Dowolne znaki bez znaków białych, od 8 do 64 znaków.',
                        'placeholder' => 'Wprowadź hasło...',
                        'autocomplete' => "off"),
                    'label_attr' => array('class' => "col-sm-2 col-form-label")
                ))
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
                    'label' => 'Dodaj nowego pracownika',
                    'attr' => array('class' => "btn btn-primary float-right")
                ))
                ->getForm();


            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $var = $form->get('haslo')->getData();
                $password = $passwordEncoder->encodePassword($pracownik, $var);
                $pracownik->setHaslo($password);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($pracownik);
                $entityManager->flush();

                return $this->redirectToRoute('worker_app/employees/list');
            }
            return $this->render('workersApp/employees/new.html.twig', array('form' => $form->createView()));
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->redirectToRoute('workers_app/no_permission');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/employees/{page<[1-9]\d*>?1}", name="worker_app/employees/list", methods={"GET"} )
     */
    public function list($page)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_MANAGER')) {
            $pageLimit = $this->getParameter('page_limit');
            $pageCount = $this->getDoctrine()->getRepository(Pracownicy::class)->getPageCountOfActive($pageLimit);
            if ($page > $pageCount and $pageCount != 0)
                return $this->redirectToRoute('worker_app/employees/list');
            else {
                $workerList = $this->getDoctrine()->getRepository(Pracownicy::class)->findActive($page, $pageLimit);
                return $this->render('workersApp/employees/list.html.twig', array('workerList' => $workerList, 'currentPage' => $page, 'pageCount' => $pageCount));
            }
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->redirectToRoute('workers_app/no_permission');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/employees/show/{id<[1-9]\d*>}", name="workers_app/employees/show", methods={"GET"})
     */
    public function show($id)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_MANAGER')) {
            $worker = $this->getDoctrine()->getRepository(Pracownicy::class)->find($id);
            return $this->render('workersApp/employees/show.html.twig', array('worker' => $worker));
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->redirectToRoute('workers_app/no_permission');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/employees/edit/{id<[1-9]\d*>}", name="workers_app/employees/edit")
     */
    public function edit(Request $request, $id)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();

            $pracownik = $this->getDoctrine()->getRepository(Pracownicy::class)->find($id);
            $form = $this->createFormBuilder($pracownik)
                ->remove('haslo')
                ->add('role', EntityType::class, array(
                    'class' => Role::class,
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                        return $er->createQueryBuilder('d')
                            ->andWhere('d.usunieto = 0 OR d.usunieto IS NULL');
                    },
                    'label' => "Rola: ",
                    'expanded' => false,
                    'multiple' => false,
                    'attr' => array("class" => "form-control", 'autocomplete' => "off"),
                    'label_attr' => array('class' => "col-sm-2 col-form-label")
                ))
                ->add('login', TextType::class, array(
                    'label' => 'Login:',
                    'attr' => array('class' => 'form-control',
                        "pattern" => "[A-Za-z0-9_\-]{5,45}",
                        "placeholder" => "Wprowadź login...",
                        'title' => 'Polskie litery, cyfry, myślniki, podkreślenia, od 5 do 45 znaków.',
                        'autocomplete' => "off"),
                    'label_attr' => array('class' => "col-sm-2 col-form-label")
                ))
                ->add('imie', TextType::class, array(
                    'label' => 'Imię:',
                    'attr' => array('class' => 'form-control',
                        "pattern" => "[A-ZŁŚ]{1}+[a-ząęółśżźćń]{2,44}",
                        'title' => 'Polskie znaki, spacja, pierwsza duża litera, od 3 do 45 znaków',
                        'placeholder' => 'Wprowadź imię...',
                        'autocomplete' => "off"),
                    'label_attr' => array('class' => "col-sm-2 col-form-label")
                ))
                ->add('nazwisko', TextType::class, array(
                    'label' => 'Nazwisko:',
                    'attr' => array('class' => 'form-control',
                        "pattern" => "[A-ZĄĘÓŁŚŻŹĆŃ]{1}+[a-ząęółśżźćń\-\s]{2,44}",
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
                    'label' => 'Edytuj pracownika',
                    'attr' => array('class' => "btn btn-primary float-right", 'style' => 'margin-right:-15px')
                ))
                ->getForm();
            if ($user->getId() == $pracownik->getId())
                $form->remove("role");


            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $pracownik = $form->getData();
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->merge($pracownik);
                $entityManager->flush();


                return $this->redirectToRoute('workers_app/employees/show', array('id' => $id));
            }
            return $this->render('workersApp/employees/edit.html.twig', array('form' => $form->createView(), 'id' => $id));
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->redirectToRoute('workers_app/no_permission');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/employees/delete/{id?<[1-9]\d*>}", name="workers_app/employees/delete", methods={"DELETE"})
     */
    public function delete($id)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            $pracownik = $this->getDoctrine()->getRepository(Pracownicy::class)->find($id);
            $pracownik->setczyAktywny(0);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->merge($pracownik);
            $entityManager->flush();
            return $this->redirectToRoute('worker_app/employees/list');
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->redirectToRoute('workers_app/no_permission');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/employees/resetPassword/{id}", name="workers_app/employees/reset_password")
     */
    public function resetPassword(Request $request, $id, UserPasswordEncoderInterface $passwordEncoder)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            $pracownik = $this->getDoctrine()->getRepository(Pracownicy::class)->find($id);
            if ($this->getUser()->getId() == $pracownik->getId())
                return $this->redirectToRoute('workers_app/no_permission');
            $form = $this->createFormBuilder($pracownik)
                ->add('haslo', TextType::class, array(
                    'label' => 'Hasło: ',
                    'attr' => array('class' => 'form-control',
                        "pattern" => "\S{8,64}",
                        'title' => 'Dowolne znaki bez znaków białych, od 8 do 64 znaków.',
                        'value' => "",
                        'placeholder' => "Wprowadź hasło...",
                        'autocomplete' => "off"),
                    'label_attr' => array('class' => "col-sm-2 col-form-label")
                ))
                ->add('save', SubmitType::class, array(
                    'label' => 'Resetuj hasło',
                    'attr' => array('class' => "btn btn-primary float-right", 'style' => "margin-right: -15px;")
                ))
                ->getForm();
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $var = $form->get('haslo')->getData();
                $password = $passwordEncoder->encodePassword($pracownik, $var);
                $pracownik->setHaslo($password);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($pracownik);
                $entityManager->flush();
                return $this->redirectToRoute('workers_app/employees/show', array('id' => $id));
            }
            return $this->render('workersApp/employees/resetPassword.html.twig', array(
                'form' => $form->createView(), 'id' => $id
            ));
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->redirectToRoute('workers_app/no_permission');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/employees/changePassword/{id}", name="workers_app/employees/change_password")
     */
    public function changePassword($id, UserPasswordEncoderInterface $passwordEncoder)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($id == $this->getUser()->getId()) {
                $pracownik = $this->getDoctrine()->getRepository(Pracownicy::class)->find($id);
                if(!$pracownik)
                    return $this->redirectToRoute('workers_app/no_permission');
                $error = "";
                if($_POST) {
                    $oldPassword = $_POST['oldPassword'];
                    $newPassword = $_POST['newPassword'];
                    $confirmPassword = $_POST['confirmPassword'];
                    if(!preg_match("/^[\S]+$/u", $newPassword)) {
                        $error = 'Hasło może składać się ze wszystkich znaków z wyłączeniem znaków białych.';
                    } else if($passwordEncoder->isPasswordValid($pracownik, $oldPassword)) {
                        if($newPassword == $confirmPassword) {
                            $newPassword = $passwordEncoder->encodePassword($pracownik, $newPassword);
                            $pracownik->setHaslo($newPassword);
                            $entityManager = $this->getDoctrine()->getManager();
                            $entityManager->persist($pracownik);
                            $entityManager->flush();
                            return $this->redirectToRoute('workers_app/main_page');
                        } else {
                            $error = "Podane hasła nie są identyczne.";
                        }
                    } else {
                        $error = "Podałeś nie poprawne stare hasło.";
                    }
                }
                return $this->render('workersApp/employees/changePassword.html.twig', array(
                    'id' => $id, 'error' => $error
                ));
            } else {
                return $this->redirectToRoute('workers_app/no_permission');
            }
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }
}
