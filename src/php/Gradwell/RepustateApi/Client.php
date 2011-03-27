<?php

/**
 * Copyright (c) 2011 Gradwell dot com Ltd.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Gradwell dot com Ltd nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     Gradwell
 * @subpackage  RepustateApi
 * @author      Stuart Herbert <stuart.herbert@gradwell.com>
 * @copyright   2011 Gradwell dot com Ltd. www.gradwell.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://gradwell.github.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Gradwell\RepustateApi;

class Client
{
        protected $apiKey = null;

        public function __construct($apiKey)
        {
                $this->setApiKey($apiKey);
        }

        /**
         * Set the API key to use to connect to repustate.com's API.
         * If you don't have any API key, get one from repustate.com.
         *
         * @param string $apiKey
         */
        protected function setApiKey($apiKey)
        {
                $this->validateApiKey($apiKey);
                $this->apiKey = $apiKey;
        }

        /**
         * Validate the API key as best we can, to spot obvious mistakes
         * Throws an exception if the key is invalid
         *
         * @param string $apiKey
         */
        protected function validateApiKey($apiKey)
        {
                // there is no upstream way to validate a key
                // so this will have to do

                if (!is_string($apiKey))
                {
                        throw new \Exception("API key '$apiKey' is not a valid string");
                }

                $expectedLength = 40;
                if (strlen($apiKey) !== $expectedLength)
                {
                        throw new \Exception("API key '$apiKey' is the wrong length; expected $expectedLength, got " . strlen($apiKey));
                }

                // if we get here, then we are as happy as we can be with the key
        }

        /**
         * Call the API's score method, to get a positive/negative score
         * for a piece of text
         *
         * If the API call fails, an exception will be thrown.
         *
         * @param string $text
         * @return float
         */
        public function callScoreForText($text)
        {
                // make the API call
                // if anything goes wrong, genericApiJsonCall will throw
                // an exception
                $result = $this->genericApiJsonCall('score', null, array('text' => $text));

                // return the score
                return (float)$result['score'];
        }

        /**
         * Call the API's score method, to get a positive/negative score
         * for text stored at a specified URL.
         *
         * If the API call fails, an exception will be thrown.
         *
         * @param string $url
         * @return float
         */
        public function callScoreForUrl($url)
        {
                // make the API call
                // if anything goes wrong, genericApiJsonCall will throw
                // an exception
                $result = $this->genericApiJsonCall('score', null, array('url' => $url));

                // return the score
                return (float)$result['score'];
        }

        /**
         * Score two or more sets of text with a single API call
         *
         * If the API call fails, an exception will be thrown
         *
         * @param array $textToScore
         * @return array
         */
        public function callBulkScore($textToScore)
        {
                // build up the parameters for this call
                $postParams = array();
                $tracker    = array();

                $i = 1;
                foreach ($textToScore as $key => $text)
                {
                        $postParams['text' . $i] = $text;
                        $tracker[$i] = $key;
                        $i++;
                }

                // make the API call
                // if anything goes wrong, genericApiJsonCall will throw
                // an exception
                $result = $this->genericApiJsonCall('bulk-score', null, $postParams);

                // return the scores
                $return = array();
                foreach ($result['results'] as $score)
                {
                        $id = $tracker[$score['id']];
                        $return[$id] = (float)$score['score'];
                }

                return $return;
        }

        /**
         * What is the sentiment for a given search term?
         *
         * If the API call fails, an exception will be thrown
         *
         * @param string $searchTerm
         * @return float
         */
        public function callSentimentProbability($searchTerm)
        {
                // make the API call
                // if anything goes wrong, genericApiJsonCall will throw
                // an exception
                $result = $this->genericApiJsonCall('prob', array('q' => $searchTerm));

                // return the result
                return (float)$result['score'];
        }

        /**
         * Search the net for the sentiment about a search term
         *
         * @param string $searchTerm
         * @param string $sentiment filter the results (one of: pos, neg or neu)
         * @param int $page which page of results do you want?
         * @param int $rpp how many results per page to return
         * @return array
         */
        public function callSearchForSentimentAbout($searchTerm, $sentiment = null, $page = null, $rpp = 100)
        {
                // build up the list of parameters
                $urlParams['q'] = $searchTerm;

                if (isset($sentiment))
                {
                        $urlParams['sentiment'] = $sentiment;
                }

                if (isset($page))
                {
                        $urlParams['page'] = $page;
                }

                if (isset($rpp))
                {
                        $urlParams['rpp'] = $rpp;
                }

                // make the API call
                // if anything goes wrong, genericApiJsonCall will throw
                // an exception
                $result = $this->genericApiJsonCall('search', $urlParams);

                // return the result
                return $result;
        }

        /**
         * Extract the adjectives being used to describe a search term online
         *
         * @param string $searchTerm the term to search for
         * @param string $sentiment filter the results; one of: pos, neg, neu
         * @return array
         */
        public function callExtractAdjectivesFromNet($searchTerm, $sentiment = null)
        {
                $urlParams = array('q' => $searchTerm, 'cloud' => 1);
                if (isset($sentiment))
                {
                        $urlParams['sentiment'] = $sentiment;
                }

                // make the API call
                // if anything goes wrong, genericApiJsonCall will throw
                // an exception
                $result = $this->genericApiJsonCall('adj', $urlParams);

                // return the results
                return $result['results'];
        }

        /**
         * Extract the adjectives from a piece of text
         *
         * @param string $text the text to analyse
         * @return array
         */
        public function callExtractAdjectivesFromText($text)
        {
                $postParams['text'] = $text;

                // make the API call
                // if anything goes wrong, genericApiJsonCall will throw
                // an exception
                $result = $this->genericApiJsonCall('adj', null, $postParams);

                // return the results
                return $result['results'];
        }

        /**
         * Extract the adjectives from content published at a URL
         *
         * @param string $url URL to find the content from
         * @return array
         */
        public function callExtractAdjectivesFromUrl($url)
        {
                $urlParams['url'] = $url;

                // make the API call
                // if anything goes wrong, genericApiJsonCall will throw
                // an exception
                $result = $this->genericApiJsonCall('adj', $urlParams);

                // return the results
                return $result['results'];
        }

        /**
         * Call any of the repustate.com APIs, even ones we do not yet
         * have a nice wrapper for, and get the results
         *
         * @param string $apiCall name of the API call to make
         * @param array $urlParams an array of params to pass to the API call
         * @param array $postParams an array of params to put in the body of the POST
         * @return array the results of calling the API
         */
        public function genericApiJsonCall($apiCall, $urlParams, $postParams = null)
        {
                // step 1: build up the API URL
                $url = $this->buildApiUrl($apiCall, $urlParams, 'json');

                // step 2: make the call
                if ($postParams == null)
                {
                        $client = new \HTTP_Request2($url, \HTTP_Request2::METHOD_GET);
                }
                else
                {
                        $client = new \HTTP_Request2($url, \HTTP_Request2::METHOD_POST);
                        foreach ($postParams as $name => $value)
                        {
                                $client->addPostParameter($name, $value);
                        }
                }

                // var_dump($url);
                $jsonResult = $client->send()->getBody();
                // var_dump($jsonResult);
                $result = json_decode($jsonResult, true);
                var_dump($result);

                // step 3: was there an error at all?
                // if there was, this method will throw a suitable exception
                $this->checkResultForErrors($url, $result);

                // step 4: no error; return the result back to the caller
                // for further decoding
                return $result;
        }

        /**
         * Create the URL that we need to call for this API call
         *
         * @param string $apiCall name of the API call to make
         * @param array $params an array of params to pass to the API call
         * @param string $format which API return format to use
         * @return string the URL to call
         */
        public function buildApiUrl($apiCall, $params, $format)
        {
                // build the initial URL
                $url = 'http://api.repustate.com/v1/'
                     . $this->apiKey
                     . '/'
                     . $apiCall
                     . '.'
                     . $format;

                // what parameters need adding?
                if (is_array($params) && count($params) > 0)
                {
                        $url .= '?';
                        $append = false;

                        foreach ($params as $name => $value)
                        {
                                if ($append)
                                {
                                        $url .= '&';
                                }
                                $append = true;

                                $url .= urlencode($name) . '=' . urlencode($value);
                        }
                }

                // all done
                return $url;
        }

        /**
         *
         * @param array $result the information returned by the API call
         */
        public function checkResultForErrors($apiUrl, $result)
        {
                // step 1: do we have a status field?
                if (!isset($result['status']))
                {
                        // uh oh - something went wrong at their end
                        //
                        // not every API call returns a 'status' field
                        // so we need to cope with that until that changes

                        if (!isset($result['errors']))
                        {
                                // no errors reported
                                // assume the call worked
                                //
                                // I am sure this will bite us on the ass
                                // one day, but am not sure how we could
                                // handle this better right now
                                return;
                        }

                        // @codeCoverageIgnoreStart
                        throw new \Exception('API result contains no status at all; something seriously went wrong');
                        // @codeCoverageIgnoreEnd
                }

                // we do :)
                //
                // step 2: does it contain good news?
                if ($result['status'] == 'OK')
                {
                        // woohoo - yes it does
                        // all done :)
                        return;
                }

                // if we get here, then our API call failed
                // the main question is ... why?

                if (!isset($result['errors']))
                {
                        // we will never know
                        //
                        // @codeCoverageIgnoreStart
                        throw new \Exception('API call failed, but result does not include error information');
                        // @codeCoverageIgnoreEnd
                }

                // we have error information
                $message = 'API call failed; error(s): ';

                foreach ($result['errors'] as $error)
                {
                        $message .= 'field ' . $error['field'] . ' ' . $error['message'] . ';';
                }

                throw new \Exception($message);
        }
}