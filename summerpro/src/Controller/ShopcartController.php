<?php

namespace App\Controller;

use App\Entity\Shopcart;
use App\Form\ShopcartType;
use App\Repository\ShopcartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/shopcart")
 */
class ShopcartController extends AbstractController
{
    /**
     * @Route("/", name="shop_cart_index", methods={"GET"})
     */
    public function index(ShopcartRepository $shopCartRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');  //login kontrolü

        $user = $this->getUser();
        $em=$this->getDoctrine()->getManager();
        $sql="SELECT p.title,p.sprice,s.* FROM shopcart s,product p WHERE s.productid=p.id AND userid= :userid";
        $statement= $em ->getConnection()->prepare($sql);
        $statement->bindValue('userid',$user->getid());
        $statement->execute();
        $shop_carts= $statement->fetchAll();


        return $this->render('shopcart/index.html.twig', [
            'shop_carts' => $shop_carts
        ]);
    }

    /**
     * @Route("/new", name="shop_cart_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $shopCart = new Shopcart();
        $form = $this->createForm(ShopcartType::class, $shopCart);
        $form->handleRequest($request);



        if ($form->isSubmitted() ) {
            $entityManager = $this->getDoctrine()->getManager();

            $user=$this->getUser();
            // $shopCart->setQuantity($request->request->get('quantity'));
            $shopCart->setUserid($user->getid());
            $entityManager->persist($shopCart);
            $entityManager->flush();

            return $this->redirectToRoute('shop_cart_index');
        }

        return $this->render('shopcart/new.html.twig', [
            'shop_cart' => $shopCart,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="shop_cart_show", methods={"GET"})
     */
    public function show(Shopcart $shopCart): Response
    {
        return $this->render('shopcart/show.html.twig', ['shop_cart' => $shopCart]);
    }

    /**
     * @Route("/{id}/edit", name="shop_cart_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Shopcart $shopCart): Response
    {
        $form = $this->createForm(ShopcartType::class, $shopCart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('shop_cart_index', ['id' => $shopCart->getId()]);
        }

        return $this->render('shopcart/edit.html.twig', [
            'shop_cart' => $shopCart,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("//{id}/del", name="shop_cart_del", methods="GET|POST")
     */
    public function del(Request $request, Shopcart $shopCart): Response
    {
        $em= $this->getDoctrine()->getManager();  //formdaki verileri ilgili alana aktarır
        $em->remove($shopCart);
        $em->flush();
        return $this->redirectToRoute('shop_cart_index');
        $this->addFlash('success','Ürün sepetten silindi !');
    }
}
