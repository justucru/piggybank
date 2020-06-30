<?php

namespace App\Controller;

use App\Entity\PiggyBank;
use App\Entity\Transaction;
use App\Form\TransactionType;
use App\Repository\PiggyBankRepository;
use App\Repository\TransactionRepository;
use App\Services\TransactionChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/transaction")
 */
class TransactionController extends AbstractController
{
    /**
     * @Route("/", name="transaction_index", methods={"GET"})
     */
    public function index(PiggyBankRepository $piggyBankRepository, TransactionRepository $transactionRepository): Response
    {
        return $this->render('transaction/index.html.twig', [
            'transactions' => $transactionRepository->findAll(),
            'piggyBanks' => $piggyBankRepository->findAll()
        ]);
    }

    /**
     * @Route("/new", name="transaction_new", methods={"GET","POST"})
     */
    public function new(Request $request, PiggyBankRepository $piggyBankRepository, TransactionChecker $transactionChecker, TransactionRepository $transactionRepository, EntityManagerInterface $entityManager): Response
    {

        $transaction = new Transaction();
        $piggyBank = $piggyBankRepository->findOneBy([]);

        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $allowedAmount = $transactionChecker->isAllowed($piggyBank, $transaction->getType(), $transaction->getAmount());

            if ($allowedAmount == false)
            {
                $this->addFlash('Danger', "No money left, FUCKER!");
            } else {
                $piggyBank->setBalance($piggyBank->getBalance() + $allowedAmount);
            };

            $entityManager->persist($transaction);
            $entityManager->flush();

            return $this->redirectToRoute('transaction_index', [
                'transactions' => $transactionRepository->findAll(),
                'piggy' => $piggyBank,
            ]);
        }

        return $this->render('transaction/new.html.twig', [
            'transaction' => $transaction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="transaction_show", methods={"GET"})
     */
    public function show(Transaction $transaction): Response
    {
        return $this->render('transaction/show.html.twig', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="transaction_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Transaction $transaction): Response
    {
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('transaction_index');
        }

        return $this->render('transaction/edit.html.twig', [
            'transaction' => $transaction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="transaction_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Transaction $transaction): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transaction->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($transaction);
            $entityManager->flush();
        }

        return $this->redirectToRoute('transaction_index');
    }
}
