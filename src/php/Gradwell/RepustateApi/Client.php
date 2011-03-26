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
         *
         * @param string $apiKey
         */
        protected function validateApiKey($apiKey)
        {
                // @TODO
        }

        /**
         * Call the API's score method, to get a positive/negative score
         * for a piece of text
         *
         * If the API call fails, an exception will be thrown.
         *
         * @param string $text
         * @return int
         */
        public function callScoreForText($text)
        {
                // make the API call
                $result = $this->genericApiJsonCall('score', null, array('text' => $text));

                // decode the result
                // if an error occurred, an exception will have already
                // been thrown

                if (!isset($result['score']))
                {
                        // something went wrong
                        throw new \Exception("expected field 'score' missing from result");
                }
                return $result['score'];
        }

        /**
         * Call the API's score method, to get a positive/negative score
         * for text stored at a specified URL.
         *
         * If the API call fails, an exception will be thrown.
         *
         * @param string $url
         * @return int
         */
        public function callScoreForUrl($url)
        {
                // make the API call
                $result = $this->genericApiJsonCall('score', null, array('url' => $url));

                // decode the result
                // if an error occurred, an exception will have already
                // been thrown

                if (!isset($result['score']))
                {
                        // something went wrong
                        throw new \Exception("expected field 'score' missing from result");
                }

                return $result['score'];
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
                $jsonResult = null;
                $result = json_decode($jsonResult);

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
                $url = 'http://api.repustate.com/v1'
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

                                $url = urlencode($name) . '=' . urlencode($value);
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
                        //throw new \Gradwell\HTTP\E_500InternalServerError("no status field returned from API call to '" . $apiUrl . "'");
                        throw new \Exception('oh dear');
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
        }
}