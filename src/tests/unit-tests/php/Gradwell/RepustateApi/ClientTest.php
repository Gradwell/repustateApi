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
                        die('Please set the REPUSTATE_KEY environment variable. See README.md for details.');
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
                $score = $client->callScoreForText($text);

                // evaluate result here
                $this->assertTrue ($score !== false);
        }

        public function testCanRetrieveSentimentScoreForUrlByJson()
        {
                // setup
                $client = new Client($this->_apiKey);
                $url = "http://www.stuartherbert.com";

                // do the test
                $score = $client->callScoreForUrl($url);

                //evaluate result here
                $this->assertTrue ($score !== false);
        }

        public function testCannotUseANonStringAsApiKey()
        {
                // setup
                $rubbishKey = 100;
                $caughtException = false;

                // do the test
                try
                {
                        $client = new Client($rubbishKey);
                }
                catch (\Exception $e)
                {
                        $caughtException = true;
                }

                // test the results
                $this->assertTrue($caughtException);
        }

        public function testCannotUseWrongLengthStringAsApiKey()
        {
                // setup
                $rubbishKey = 'this string is too short';
                $caughtException = false;

                // do the test
                try
                {
                        $client = new Client($rubbishKey);
                }
                catch (\Exception $e)
                {
                        $caughtException = true;
                }

                // test the results
                $this->assertTrue($caughtException);
        }

        public function testCannotUseNullTextForScoring()
        {
                // setup
                $client = new Client($this->_apiKey);
                $text = null;
                $caughtException = false;

                // do the test
                try
                {
                        $score = $client->callScoreForText($text);
                }
                catch (\Exception $e)
                {
                        $caughtException = true;
                }

                // evaluate result here
                $this->assertTrue ($caughtException);
        }

        public function testCannotUseNullUrlForScoring()
        {
                // setup
                $client = new Client($this->_apiKey);
                $url = null;
                $caughtException = false;

                // do the test
                try
                {
                        $score = $client->callScoreForUrl($url);
                }
                catch (\Exception $e)
                {
                        $caughtException = true;
                }

                // evaluate result here
                $this->assertTrue ($caughtException);
        }

        public function testCanExtractScoresForBulkText()
        {
                // setup
                // text is from Stuart Herbert's blog
                $textToScore = array (
                        'extract1' => "Many old-skool developers choose to work largely from the command-line. You can happily run PHPUnit by hand from the command line yourself each time, but if you’re taking advantage of the extra data that PHPUnit can report back on, that soon gets to be a lot of typing! This is where the build.xml file in our skeleton comes in handy …",
                        'extract2' => "If the command-line isn’t for you, don’t worry … the component skeleton is also designed to make it very easy to run your unit tests from inside Netbeans. I’m assuming that it will be just as easy to do this from other IDEs that support PHPUnit, but I haven’t tested any myself.",
                );

                // do the test
                $client = new Client($this->_apiKey);
                $scores = $client->callBulkScore($textToScore);

                // evalute the results
                $this->assertTrue(isset($scores['extract1']));
                $this->assertTrue(isset($scores['extract2']));
        }

        public function testCanFindSentimentProbabilityForTerm()
        {
                // setup
                $searchTerm = 'Stuart Herbert';

                // do the test
                $client = new Client($this->_apiKey);
                $score = $client->callSentimentProbability($searchTerm);

                // evaluate the results
                $this->assertTrue(is_float($score));
        }

        public function testCanSearchForSentimentForTerm()
        {
                // setup
                $searchTerm = 'Stuart Herbert';

                // do the test
                $client = new Client($this->_apiKey);
                $results = $client->callSearchForSentimentAbout($searchTerm);

                // evaluate the results
                //
                // we can make sure that the results are internally
                // consistent
                $this->assertTrue(isset($results['number_of_results']));
                $this->assertTrue(isset($results['results']));
                $this->assertEquals($results['number_of_results'], count($results['results']));
        }

        public function testCanSearchForTypeOfSentimentForTerm()
        {
                // setup
                $searchTerm = 'Stuart Herbert';

                // do the test
                $client = new Client($this->_apiKey);
                $results = $client->callSearchForSentimentAbout($searchTerm, 'pos');

                // evaluate the results
                //
                // we can make sure that the results are internally
                // consistent
                $this->assertTrue(isset($results['number_of_results']));
                $this->assertTrue(isset($results['results']));
                $this->assertEquals($results['number_of_results'], count($results['results']));
        }

        public function testCanSearchForPagesOfSentimentForTerm()
        {
                // setup
                $searchTerm = 'Microsoft';

                // do the test
                $client = new Client($this->_apiKey);
                $results1 = $client->callSearchForSentimentAbout($searchTerm, null);
                $results2 = $client->callSearchForSentimentAbout($searchTerm, null, 2);

                // evaluate the results
                //
                // we can make sure that the results are internally
                // consistent
                $this->assertTrue(isset($results1['number_of_results']));
                $this->assertTrue(isset($results1['results']));
                $this->assertEquals($results1['number_of_results'], count($results1['results']));
                $this->assertTrue(isset($results2['number_of_results']));
                $this->assertTrue(isset($results2['results']));
                $this->assertEquals($results2['number_of_results'], count($results2['results']));

                $this->assertNotSame($results1, $results2);
        }

        public function testCanSearchForAdjectivesAboutTerm()
        {
                // setup
                $searchTerm = 'Microsoft';

                // do the test
                $client = new Client($this->_apiKey);
                $results = $client->callExtractAdjectivesFromNet($searchTerm);

                // evaluate the results
                $this->assertTrue(count($results) > 0);
                
        }

        public function testCanSearchForAdjectivesWithSentimentAboutTerm()
        {
                // setup
                $searchTerm = 'Microsoft';

                // do the test
                $client = new Client($this->_apiKey);
                $results = $client->callExtractAdjectivesFromNet($searchTerm, 'pos');

                // evaluate the results
                $this->assertTrue(count($results) > 0);
        }

        public function testCanSearchForAdjectivesFromText()
        {
                // setup
                // text is from Stuart Herbert's blog
                $textToScore = "Many old-skool developers choose to work largely from the command-line. You can happily run PHPUnit by hand from the command line yourself each time, but if you’re taking advantage of the extra data that PHPUnit can report back on, that soon gets to be a lot of typing! This is where the build.xml file in our skeleton comes in handy …";

                // do the test
                $client = new Client($this->_apiKey);
                $results = $client->callExtractAdjectivesFromText($textToScore);

                // evaluate the results
                $this->assertTrue(count($results) > 0);
        }

        public function testCanSearchForAdjectivesFromUrl()
        {
                // setup
                $url = 'http://www.stuartherbert.com';

                // do the test
                $client = new Client($this->_apiKey);
                $results = $client->callExtractAdjectivesFromUrl($url);

                // evaluate the results
                $this->assertTrue(count($results) > 0);
        }
}