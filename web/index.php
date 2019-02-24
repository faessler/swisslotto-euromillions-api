<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get dependencies
require "../vendor/autoload.php";
use PHPHtmlParser\Dom;



/**
 * GET LOTTO URL QUERY
 *
 * This function will return the
 * value of the transmitted lotto
 * URL query.
 *
 * @requires
 *
 * @return string
 */
function getLottoUrlQuery() {
    $allowedQueries = ['swisslotto', 'euromillions'];
    $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    // Return false if url has no queries
    if (!parse_url($url, PHP_URL_QUERY)) {
        return false;
    }

    // Get queries
    $parts = parse_url($url);
    parse_str($parts['query'], $query);

    // Return false if there is no lotto query
    if (!array_key_exists('lotto', $query)) {
        return false;
    }
    $lotto = $query['lotto'];

    // Return false if the lotto query is not in the allowed queries list
    if (!in_array($lotto, $allowedQueries)) {
        return false;
    }

    // Return lotto query
    return $lotto;
}



/**
 * GET DOM FROM URL
 *
 * This function will return the
 * from a url.
 *
 * @requires
 *
 * @return object
 */
function getDomFromUrl($lotto) {
    switch ($lotto) {
        case 'swisslotto':
            $url = 'https://www.swisslos.ch/en/swisslotto/information/winning-numbers/winning-numbers.html';
            break;
        case 'euromillions':
            $url = 'https://www.swisslos.ch/en/euromillions/information/winning-numbers/winning-numbers.html';
            break;
        default:
            $url = '';
            break;
    }
    // Get DOM from URL
    $dom = new Dom;
    $dom->loadFromUrl($url);

    // Return DOM
    return $dom;
}



/**
 * GET NUMBERS
 *
 * This function will return the
 * lotto numbers from the DOM
 * as an array.
 *
 * @requires object
 *
 * @return array
 */
function getNumbers($dom, $type='') {
    // Get numbers as DOM
    switch ($type) {
        case 'lucky':
            $numbersDOM = $dom->find('.filter-results .quotes__game', 0)->find('.actual-numbers__numbers .actual-numbers__number___lucky');
            break;
        case 'star':
            $numbersDOM = $dom->find('.filter-results .quotes__game', 0)->find('.actual-numbers__numbers .actual-numbers__number___superstar');
            break;
        case '2ndChance':
            if (!$dom->find('.filter-results .quotes__game', 1)) {
                return [];
            }
            $numbersDOM = $dom->find('.filter-results .quotes__game', 1)->find('.actual-numbers__numbers .actual-numbers__number___normal');
            break;
        default:
            $numbersDOM = $dom->find('.filter-results .quotes__game', 0)->find('.actual-numbers__numbers .actual-numbers__number___normal');
            break;
    }

    // Get numbers as array
    $numbersArray = [];
    foreach ($numbersDOM as $number) {
        $numberData = str_replace(' ', '', strip_tags($number->find('span')));
        array_push($numbersArray, $numberData);
    }

    return $numbersArray;
}



/**
 * GET DATE OF DRAWING
 *
 * This function will return the
 * lotto number drawing date.
 *
 * @requires object
 *
 * @return string
 */
function getDateOfDrawing($dom) {
    // Get date as DOM
    $dateDom = $dom->find('.filter .filter__form #currentDate')->getAttribute('value');

    // Get date as string
    $dateString = $dateDom;

    // Return date string
    return $dateString;
}



/**
 * GET JSON
 *
 * This function will return the
 * JSON which can be printed for
 * the api call.
 *
 * @requires
 *
 * @return string
 */
function getJson() {
    // Get data
    $numbersNormal = getNumbers(getDomFromUrl(getLottoUrlQuery()));
    $numbersNormal2ndChance = getNumbers(getDomFromUrl(getLottoUrlQuery()), '2ndChance');
    $numbersLucky = getNumbers(getDomFromUrl(getLottoUrlQuery()), 'lucky');
    $numbersStar = getNumbers(getDomFromUrl(getLottoUrlQuery()), 'star');
    $date = getDateOfDrawing(getDomFromUrl(getLottoUrlQuery()));

    // Create JSON
    $json = json_encode([
        'numbers' => [
            'normal' => $numbersNormal,
            '2ndChance' => $numbersNormal2ndChance,
            'lucky' => $numbersLucky,
            'star' => $numbersStar
        ],
        'date' => $date
    ]);

    // Return JSON
    return $json;
}



/**
 * OUTPUT
 *
 * This script will finally output the page content.
 * It will look for a file like 'swisslotto-dd.mm.yyyy.json'
 * and output it's content. If the file doesn't exists it
 * will create it and then output the json.
 *
 */
if (getLottoUrlQuery()) {
    $file = 'drawings/'.getLottoUrlQuery().'-'.getDateOfDrawing(getDomFromUrl(getLottoUrlQuery())).'.json';
    if (file_exists($file)) {
        // Get content from local file
        $content = file_get_contents($file);
    } else {
        // Delete all old files matching "lotto-*.json"
        array_map('unlink', glob('drawings/'.getLottoUrlQuery().'-*.json'));

        // Get content from swisslos.ch site
        $content = getJson();

        // Put content into new JSON file
        file_put_contents($file, $content);
    }

    print_r($content);
}