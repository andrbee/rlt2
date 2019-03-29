<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Auction;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function index()
    {
		$auction = new Auction();
		$auctions = $auction->getList();

		return $this->render('default/index.html.twig',[
			'title' => 'Аукционы Минской области',
			'auctions' => $auctions
		]);
    }
}
