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

class ClientTest extends \PHPUnit_Framework_TestCase
{
        public function setUp()
        {
                // we get the API key from the runtime environment
                //
                // this ensures that we do not commit our API key
                // to public source control
                $apiKey = getenv('REPUSTATE_KEY');

                if (false === $apiKey)
                {
                        die('Please set the REPUSTATE_KEY environment variable');
                }

                $this->_apiKey = $apiKey;
        }

        public function testCanCreateClient()
        {
                // create the client with a valid key
                $client = new Client($this->_apiKey);

                // did it work?
                $this->assertTrue($client instanceof Client);
        }

        public function testCanRetrieveSentimentScoreForTextByJson()
        {
                // setup
                $client = new Client($this->_apiKey);
                $text = "this is a happy piece of text";

                // do the test
                $score = $client->callScoreForText($text, 'json');

                // evaluate result here
                $this->assertTrue ($score !== false);
        }

        public function testCanRetrieveSentimentScoreForUrlByJson()
        {
                // setup
                $client = new Client($this->_apiKey);
                $url = "http://www.stuartherbert.com";

                // do the test
                $score = $client->callScoreForUrl($url, 'json');

                //evaluate result here
                $this->assertTrue ($score !== false);
        }
}