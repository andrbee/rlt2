<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Auction;

/**
 * @Route("/auctions")
 */
class AuctionController extends Controller
{
	/**
	 * @Route("/")
	 */
	public function index()
	{

		$auction = new Auction();
		$auctions = $auction->getList();

		return $this->render('auction/index.html.twig', [
			'title' => 'Аукционы Минской области',
			'auctions' => $auctions
		]);
	}

	/**
	 * @Route("/ajaxMaps")
	 */
	public function ajaxMaps(Request $request)
	{
		if ($request->isXmlHttpRequest() && $request->get('urls')) {

			$auction = new Auction();
			$maps = $auction->getMaps($request->get('urls'));

//			return new Response($body, 200, [
//				'Content-Type' => $results[0]['value']->getHeader('Content-Type')[0]
//			]);
			return new JsonResponse([
					'status' => 'OK',
					'data' => [
						'maps' => $maps
					]
				]
			);
		} else {
			return new JsonResponse([
					'status' => 'ER',
					'message' => 'Request is empty !'
				]
			);
		}
	}
}
