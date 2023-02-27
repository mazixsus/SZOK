<?php
/**
 * Created by PhpStorm.
 * User: Mateusz
 * Date: 22.11.2018
 * Time: 14:19
 */

namespace App\Controller;


use App\Entity\Uzytkownicy;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class UserController extends Controller
{
    /**
     * @Route("/registration", name="clients_app/registration", methods={"GET", "POST"})
     */
    public function new(Request $request, UserPasswordEncoderInterface $passwordEncoder, \Swift_Mailer $mailer)
    {
        if ($this->isGranted('ROLE_USER') and AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('clients_app/logout_page');
        }
        if (!$this->isGranted('ROLE_USER')) {
            $user = new Uzytkownicy();
            $form = $this->createFormBuilder($user)
                ->add('login', TextType::class, array(
                    'label' => 'Login:',
                    'attr' => array('class' => 'form-control',
                        "pattern" => "[A-Za-z0-9\-_]{5,45}",
                        "placeholder" => "Wprowadź login...",
                        'title' => 'Polskie litery, cyfry, myślniki, podkreślenia, od 5 do 45 znaków.',
                        'autocomplete' => "off"),
                    'label_attr' => array('class' => "col-sm-2 col-form-label")
                ))
                ->add('haslo', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'first_options' => array(
                        'label' => 'Hasło:',
                        'attr' => array('class' => 'form-control',
                            "pattern" => "\S{8,64}",
                            'title' => 'Dowolne znaki bez znaków białych, od 8 do 64 znaków.',
                            'placeholder' => 'Wprowadź hasło...',
                            'autocomplete' => "off"),
                        'label_attr' => array('class' => "col-sm-2 col-form-label")),
                    'second_options' => array(
                        'label' => 'Powtórz hasło:',
                        'attr' => array('class' => 'form-control',
                            "pattern" => "\S{8,64}",
                            'title' => 'Dowolne znaki bez znaków białych, od 8 do 64 znaków.',
                            'placeholder' => 'Powtórz hasło...',
                            'autocomplete' => "off"),
                        'label_attr' => array('class' => "col-sm-2 col-form-label")),
                    'invalid_message' => 'Hasła są nie zgodne.',
                    'required' => true
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
                ->add('czykobieta', ChoiceType::class, array(
                    'choices' => array('Mężczyzna' => false, 'Kobieta' => true),
                    'label' => 'Pleć:',
                    'expanded' => true,
                    'multiple' => false,
                    'label_attr' => array('class' => 'col-sm-2')
                ))
                ->add('save', SubmitType::class, array(
                    'label' => 'Zarejestruj się',
                    'attr' => array('class' => "btn btn-primary float-right")
                ))
                ->getForm();
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $date = new \DateTime(date('Y-m-d'));
                $user->setDatarejestracji($date);
                $var = $form->get('haslo')->getData();
                $password = $passwordEncoder->encodePassword($user, $var);
                $user->setHaslo($password);
                $entityManager->persist($user);
                $entityManager->flush();

                $message = (new \Swift_Message('Potwierdzenie rejestracji'))
                    ->setFrom('szok.smtp@gmail.com')
                    ->setTo('adtunm@gmail.com')
                    ->setBody(
                        $this->renderView('clientsApp/mails/mailRegistration.html.twig',
                            array('user' => $user)
                        ),
                        'text/html'
                    );
                $mailer->send($message);

                return $this->redirectToRoute('clients_app/main_page');
            }
            return $this->render('clientsApp/users/registration.html.twig', array('form' => $form->createView()));
        }
    }

    /**
     * @Route("/user/edit", name="clients_app/user/edit", methods={"GET", "POST"})
     */
    public function edit(Request $request)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('clients_app/logout_page');
        }
        if ($this->isGranted('ROLE_USER')) {
            $user = $this->getDoctrine()->getRepository(Uzytkownicy::class)->find($this->getUser()->getId());
            $form = $this->createFormBuilder($user)
                ->remove('haslo')
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
                ->add('czykobieta', ChoiceType::class, array(
                    'choices' => array('Mężczyzna' => false, 'Kobieta' => true),
                    'label' => 'Pleć:',
                    'expanded' => true,
                    'multiple' => false,
                    'label_attr' => array('class' => 'col-sm-2')
                ))
                ->add('save', SubmitType::class, array(
                    'label' => 'Zatwierdź',
                    'attr' => array('class' => "btn btn-primary float-right")
                ))
                ->getForm();
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user = $form->getData();
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->merge($user);
                $entityManager->flush();
                return $this->redirectToRoute('clients_app/main_page');
            }

            return $this->render('clientsApp/users/userEdit.html.twig', array('form' => $form->createView()));
        }
    }

    /**
     * @Route("/user/passwordEdit", name="clients_app/user/password_edit", methods={"GET", "POST"})
     */
    public function editPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('clients_app/logout_page');
        }
        if ($this->isGranted('ROLE_USER')) {

            $user = $this->getUser();
            $error = "";
            if($_POST) {
                $oldPassword = $_POST['oldPassword'];
                $newPassword = $_POST['newPassword'];
                $confirmPassword = $_POST['confirmPassword'];
                if(!preg_match("/^[\S]+$/u", $newPassword)) {
                    $error = 'Hasło może składać się ze wszystkich znaków z wyłączeniem znaków białych.';
                } else if($passwordEncoder->isPasswordValid($user, $oldPassword)) {
                    if($newPassword == $confirmPassword) {
                        $newPassword = $passwordEncoder->encodePassword($user, $newPassword);
                        $user->setHaslo($newPassword);
                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($user);
                        $entityManager->flush();
                        return $this->redirectToRoute('clients_app/main_page');
                    } else {
                        $error = "Podane hasła nie są identyczne.";
                    }
                } else {
                    $error = "Podałeś nie poprawne stare hasło.";
                }
            }
            return $this->render('clientsApp/users/passwordEdit.html.twig', array(
                'error' => $error
            ));

        }
        return $this->redirectToRoute('clients_app/main_page');
    }

    /**
     * @Route("/passwordReset", name="clients_app/password_reset", methods={"GET", "POST"})
     */
    public function resetPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder, \Swift_Mailer $mailer)
    {
        $user = null;
        $error = false;

        $form = $this->createFormBuilder($user)
            ->add('login', TextType::class, array(
                'label' => 'Login:',
                'attr' => array('class' => 'form-control',
                    "pattern" => "[A-Za-z0-9\-_]{5,45}",
                    "placeholder" => "Wprowadź login...",
                    'title' => 'Polskie litery, cyfry, myślniki, podkreślenia, od 5 do 45 znaków.',
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
            ->add('save', SubmitType::class, array(
                'label' => 'Resetuj hasło',
                'attr' => array('class' => "btn btn-primary float-right")
            ))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $login = $form->get('login')->getData();
            $user = $this->getDoctrine()->getRepository(Uzytkownicy::class)->findOneBy(array('email' => $email, 'login' => $login));
            if (!$user) {
                $error = true;
                return $this->render('clientsApp/users/passwordReset.html.twig', array('form' => $form->createView(), 'error' => $error));
            } else {
                $newPassword = $this->random_str(15);
                $entityManager = $this->getDoctrine()->getManager();
                $password = $passwordEncoder->encodePassword($user, $newPassword);
                $user->setHaslo($password);
                $entityManager->merge($user);
                $entityManager->flush();

                $message = (new \Swift_Message('Twoje hasło zostało zresetowane!'))
                    ->setFrom('szok.smtp@gmail.com')
                    ->setTo('adtunm@gmail.com')
                    ->setBody(
                        $this->renderView('clientsApp/mails/passwordReset.html.twig',
                            array('user' => $user, 'password' => $newPassword)
                        ),
                        'text/html'
                    );
                $mailer->send($message);

                return $this->redirectToRoute('clients_app/main_page');
            }
        }
        return $this->render('clientsApp/users/passwordReset.html.twig', array('form' => $form->createView(), 'error' => $error));
    }

    public function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=')
    {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces [] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}