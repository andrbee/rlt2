<?php
/**
 * Created by PhpStorm.
 * User: balashov_a
 * Date: 15.03.2019
 * Time: 18:03
 */

namespace App\Entity;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class Auction
{
	const HOST_RESOURCE = 'http://rlt.by';
	const URL_LIST_AUCTION = '/aukcion/search.php';
	const HOST_PROXY = '192.168.15.240:3128';

	public function getList()
	{
		$html = $this->guzzleRequest('POST', self::HOST_RESOURCE . self::URL_LIST_AUCTION,
			$this->getParamsForAuctions());
		$crawler = new Crawler((string)$html);
		$items = $crawler->filter('.preview');
		$list = [];

		$items->each(function (Crawler $node) use (&$urls, &$list) {
			$title = $node->filter('.spanfield0');
			$title = trim(str_replace($title->filter('strong')->text(), '', $title->text()));

			$dateAndTime = $node->filter('.spanfield2');
			$dateAndTime = trim(str_replace($dateAndTime->filter('strong')->text(), '', $dateAndTime->text()));

			$region = $node->filter('.spantable1sub1');
			$region = trim(str_replace($region->filter('strong')->text(), '', $region->text()));

			$url = $node->filter('.spantable.link a');
			$url = $url->attr('href');
			$id = str_replace('=', '', strstr($url, '='));

			$list[$id] = array(
				'title' => $title,
				'dateAndTime' => $dateAndTime,
				'reqion' => $region,
				'url' => self::HOST_RESOURCE . $url,
				'id' => $id
			);
		});

		return $this->getAuctions($list);
	}

	private function getAuctions($list)
	{
		$client = new Client();
		$auctions = [];

		foreach ($list as $id => $item) {
			$response = $client->request('GET', $item['url']);

			$auctions[$id] = $item;
			$auctions[$id]['areas'] = $this->parseAuction((string)$response->getBody());
		}

		return $auctions;
	}

	private function parseAuction($content)
	{
		$auction = null;
		$idsArea = [];

		$crawler = new Crawler($content);
		$item = $crawler->filter('.descriptionOpisanie');

		$header = 'tr:first-of-type';

		$headerFields = $item->filter('table ' . $header . ' td')->each(function (Crawler $node) {
			return str_replace('\r\n', '', trim($node->text()));
		});

		foreach ($headerFields as $key => $id) {
			switch ($id) {
				case '№ лота':
					$idsArea[$key] = 'num';
					break;
				case 'Адрес земельного участка':
					$idsArea[$key] = 'address';
					break;
				case 'Кадастровый номер земельного участка':
					$idsArea[$key] = 'cadNum';
					break;
				case 'Площадь земельного участка, га':
					$idsArea[$key] = 'areaValue';
					break;
				case 'Целевое назначение земельного участка':
					$idsArea[$key] = 'goalPurpose';
					break;
				case 'Инженерная инфраструктура *':
					$idsArea[$key] = 'infrastructure';
					break;
				case 'Расходы по подготовке документации для проведения аукциона, бел. руб.':
					$idsArea[$key] = 'costPrepareDocs';
					break;
				case 'Начальная цена земельного участка, бел. руб.':
					$idsArea[$key] = 'startPrice';
					break;
				case 'Сумма задатка, бел. руб.':
					$idsArea[$key] = 'depositPrice';
					break;
				default:
					$idsArea[$key] = $id;
					break;
			}
		}

		$areas = $item->filter('table tr:not(' . $header . ')')->each(function (Crawler $node) use (&$idsArea) {
			$area = [];
			$node->filter('td')->each(function (Crawler $properties, $i) use (&$area, &$idsArea) {
				$value = str_replace('\r\n', '', trim($properties->text()));
				$value = in_array($idsArea[$i],
					['depositPrice', 'startPrice', 'costPrepareDocs']) ? str_replace('&nbsp;',
					'', htmlentities($value)) : $value;
				$area[$idsArea[$i]] = $value;
			});
			return $area;
		});
		return [
			'headerAreas' => $headerFields,
			'areas' => $areas
		];
	}

	public function getMaps($urls)
	{
		return $this->getAuctions($urls);
	}

	private function guzzleRequest($format, $url, $postdata = '')
	{
		$config = [
			'form_params' => $postdata
		];
		$client = new Client();
		$response = $client->request($format, $url, $config);
		return $response->getBody();
	}

	private function getParamsForAuctions()
	{
		return [
			'select_name_table1' => 0,
			'select_name_table1sub1' => 0,
			'select_name_table3' => 4,
			'field1_from' => 0,
			'field1_to' => 1000000000000,
		];
	}


}