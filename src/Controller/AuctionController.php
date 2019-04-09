<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
		$count = count($auctions);

		return $this->render('auction/index.html.twig', [
			'title' => $count ? "{$count} аукционов найдено" : 'Нет аукционов в Минской области',
			'auctions' => $auctions
		]);
	}

	/**
	 * @Route("/ajaxMaps")
	 */
	public function ajaxMaps(Request $request)
	{
		$urls = $request->get('urls');

		if ($request->isXmlHttpRequest() && $urls) {
			$auction = new Auction();
			$maps = $auction->getMaps($urls);

			$response = [
				'status' => 'OK',
				'data' => [
					'maps' => $maps
				]
			];
		} else {
			$response = [
				'status' => 'ER',
				'message' => 'Request is empty !'
			];
		}
		return new JsonResponse($response);
	}
}
