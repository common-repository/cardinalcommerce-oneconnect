<?php

ini_set('display_errors', 'on');
error_reporting(E_ALL);

require_once( __DIR__ . "/../vendor/autoload.php" );

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\CartSettings;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\CartOrderDetails;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;

use \CardinalCommerce\Payments\Carts\Common\Songbird\SongbirdContext;

require_once( __DIR__ . "/SongbirdBaseTestCase.php" );

class SongbirdRendererTest extends SongbirdBaseTestCase {

    public function testSongbirdContextCreateServerJWT() {
        $logger = $this->getLoggerMock();

        $mocks = $this->getSongbirdMocks();

        $cartOrderDetails = $mocks->cartOrderDetails;

        $mocks->serverJWTCreator->expects( $this->once() )
            ->method('create')
            ->with(
                $this->callback(function($arg_cartOrderDetails) use ($cartOrderDetails) {
                    return $arg_cartOrderDetails === $cartOrderDetails;
                })
            )
            ->will( $this->returnValue( "SERVERJWT" ));

        $songbirdContext = $this->createSongbirdContext(
            $logger,
            $mocks->cartSettings,
            $mocks->songbirdRenderer,
            $mocks->songbirdCartScriptRenderer,
            $mocks->serverJWTCreator,
            $mocks->cartPaymentDetailsPage
        );

        /*
        $serverJWT = $songbirdContext->renderServerProvidedHiddenInputs(
            $cartOrderDetails
        );
        */

        $serverJWT = $songbirdContext->createServerJWT(
            $cartOrderDetails
        );
            
        $this->assertEquals( $serverJWT, 'SERVERJWT' );
    }
}