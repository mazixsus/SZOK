<?php

namespace App\Controller;

use App\Entity\Vouchery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class VouchersController extends Controller
{

    /**
     * @Route("/vouchers/{page<[1-9]\d*>?1}", name="workers_app/vouchers", methods={"GET"})
     */
    public function list($page)
    {
        if(AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN')) {
            $pageLimit = $this->getParameter('page_limit');
            $pageCount = $this->getDoctrine()->getRepository(Vouchery::class)->getPageCount($pageLimit);

            if($page > $pageCount and $pageCount != 0)
                return $this->redirectToRoute('workers_app/vouchers');
            else {
                $vouchers = $this->getDoctrine()->getRepository(Vouchery::class)->findPage($page, $pageLimit);
                return $this->render('workersApp/vouchers/list.html.twig', array('vouchers' => $vouchers, 'currentPage' => $page, 'pageCount' => $pageCount));
            }
        } else if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('workers_app/no_permission');
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/vouchers/show/{timestamp<[1-9]\d*>?1}", name="workers_app/vouchers/pdf", methods={"GET"})
     */
    public function showVouchers($timestamp)
    {
        if(AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN')) {
            $datetime = new \DateTime();
            $datetime->setTimestamp($timestamp);
            $vouchers = $this->getDoctrine()->getRepository(Vouchery::class)->findByCzaswygenerowania($datetime);

            if(!$vouchers)
                return $this->redirectToRoute('workers_app/no_permission');
            else {
                $sum = 0;
                foreach($vouchers AS $voucher) {
                    if(!$voucher->getCzywykorzystany()) $sum++;
                }
                if(!$sum) {
                    return $this->redirectToRoute('workers_app/no_permission');
                }

                $snappy = $this->get('knp_snappy.pdf');
                $html = $this->renderView('workersApp/vouchers/pdf.html.twig', ['vouchers' => $vouchers]);
                return new Response(
                    $snappy->getOutputFromHtml($html),
                    200,
                    array(
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="ticket.pdf"'
                    )
                );
            }
        } else if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('workers_app/no_permission');
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/vouchers/add", name="workers_app/vouchers/add", methods={"GET", "POST"})
     */
    public function add(Request $request)
    {
        if(AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN')) {
            $data = array(
                'number' => NULL,
                'isMoney' => NULL,
                'value' => NULL,
                'start' => NULL,
                'end' => NULL,
            );
            $errors = array(
                'number' => NULL,
                'value' => NULL,
                'start' => NULL,
                'end' => NULL,
            );
            $form = $this->getForm($data);
            $form->handleRequest($request);
            if($form->isSubmitted()) {
                $data = $form->getData();
                if($data['number'] <= 0)
                    $errors['number'] = 'Liczba voucher??w nie mo??e by?? mniejsza od 1.';
                else if($data['number'] > 150)
                    $errors['number'] = 'Liczba voucher??w nie mo??e by?? wi??ksza ni?? 150.';

                if($data['value'] < 0.01)
                    $errors['value'] = 'Warto???? zni??ki musi by?? wi??ksza od zera.';
                else if($data['value'] > 100.00)
                    $errors['value'] = 'Warto???? zni??ki nie powinna przekracza?? 100.00.';

                if($data['start']->format('Y-m-d') <= date('Y-m-d'))
                    $errors['start'] = 'Vouchery powinniy by?? wa??ne nie wcze??niej ni?? od jutra.';

                if($data['end']->format('Y-m-d') < $data['start']->format('Y-m-d'))
                    $errors['end'] = "Koniec wa??no??ci voucher??w nie mo??e by?? wcze??niej ni?? jej pocz??tek.";

                if($form->isValid() and !$errors['number'] and !$errors['value'] and !$errors['start'] and !$errors['end']){
                    $this->pushVouchers($data);
                    return $this->redirectToRoute('workers_app/vouchers');
                }
            }
            return $this->render('workersApp/vouchers/add.html.twig', array('form' => $form->createView(), 'errors' => $errors));
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
            ->add('number', IntegerType::class, array(
                'label' => 'Liczba voucher??w:',
                'label_attr' => array('class' => 'col-sm-2'),
                'invalid_message' => 'Warto???? musi by?? liczb??',
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => '000',
                    'min' => '1',
                    'max' => '150',
                    'step' => '1',
                    'title' => 'Liczba wi??ksza od 0, maksymalnie 150',
                    'autocomplete' => 'off'
                )
            ))
            ->add('isMoney', ChoiceType::class, array(
                'choices' => array('Procentowo' => false, 'Kwotowo' => true),
                'label' => 'Spros??b wyra??enia zni??ki:',
                'expanded' => true,
                'multiple' => false,
                'choice_attr' => array('class' => 'radio-inline'),
                'label_attr' => array('class' => 'col-sm-2'),
                'attr' => array('class' => 'col-sm-10')
            ))
            ->add('value', NumberType::class, array(
                'label' => 'Zni??ka:',
                'label_attr' => array('class' => 'col-sm-2'),
                'scale' => 2,
                'invalid_message' => 'Warto???? musi by?? liczb??',
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => '000.00',
                    'title' => 'Liczba wi??ksza od 0, maksymalnie 100, do 2 cyfr po przecinku',
                    'autocomplete' => 'off'
                )
            ))
            ->add('start', DateType::class, array(
                'label' => 'Pocz??tek wa??no??ci:',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'label_attr' => array('class' => 'col-sm-2'),
                'attr' => array(
                    'class' => 'form-control',
                    'title' => 'Data nie wcze??niejsza ni?? jutro'
                )
            ))
            ->add('end', DateType::class, array(
                'label' => 'Koniec wa??no??ci:',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'label_attr' => array('class' => 'col-sm-2'),
                'attr' => array(
                    'class' => 'form-control',
                    'title' => 'Data niewcze??niejsza ni?? pocz??tek wa??no??ci'
                )
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Zapisz',
                'attr' => array('class' => 'btn btn-primary')
            ))
            ->getForm();
    }

    /**
     * @param array $data
     */
    private function pushVouchers(array $data){
        $entityManager = $this->getDoctrine()->getManager();
        $generationDate = new \DateTime();
        for($i=0; $i<$data['number']; $i++){
            $voucher = new Vouchery();
            $voucher->setCzywykorzystany(false);
            $voucher->setLosowecyfry("" . rand(0, 9) . rand(0, 9) . rand(0, 9));
            $voucher->setCyfrakontrolna(0);
            $voucher->setWartosc($data['value']);
            $voucher->setCzykwotowa($data['isMoney']);
            $voucher->setPoczatekpromocji($data['start']);
            $voucher->setKoniecpromocji($data['end']);
            $voucher->setCzaswygenerowania($generationDate);
            $entityManager->persist($voucher);
        }
        $entityManager->flush();

        $vouchers = $this->getDoctrine()->getRepository(Vouchery::class)->findByCzaswygenerowania($generationDate);
        foreach($vouchers AS $voucher){
            $voucher->recalculateControlDigit();
            $entityManager->merge($voucher);
        }
        $entityManager->flush();
    }
}
