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

class SongbirdContextTest extends SongbirdBaseTestCase {

    public function testSongbirdContextConfigure() {
        $logger = $this->getLoggerMock();

        $mocks = $this->getSongbirdMocks();

        $mocks->cartSettings->expects( $this->once() )
            ->method('getSongbirdLoggingEnabled')
            ->will( $this->returnValue( true ));

        $songbirdContext = $this->createSongbirdContext(
            $logger,
            $mocks->cartSettings,
            $mocks->songbirdRenderer,
            $mocks->songbirdCartScriptRenderer,
            $mocks->serverJWTCreator,
            $mocks->cartPaymentDetailsPage
        );

        $config = $songbirdContext->getConfigureOptions();

        $this->assertEquals(
            $config,
            (object) array(
                'logging' => (object) array(
                    'level' => 'verbose'
                )
            )
        );
    }

    public function testSongbirdContextRenderServerProvidedHiddenInputs() {
        $logger = $this->getLoggerMock();

        $mocks = $this->getSongbirdMocks();

        $cartOrderDetails = $mocks->cartOrderDetails;

        $mocks->songbirdRenderer->expects( $this->once() )
            ->method('renderServerProvidedHiddenInputs')
            ->with(
                $this->callback(function($arg_songbirdContext) {
                    return true;
                }),
                $this->callback(function($arg_cartOrderDetails) use ($cartOrderDetails) {
                    return $arg_cartOrderDetails === $cartOrderDetails;
                })
            )
            ->will( $this->returnValue( "HTML" ));

        $songbirdContext = $this->createSongbirdContext(
            $logger,
            $mocks->cartSettings,
            $mocks->songbirdRenderer,
            $mocks->songbirdCartScriptRenderer,
            $mocks->serverJWTCreator,
            $mocks->cartPaymentDetailsPage
        );

        $config = $songbirdContext->getConfigureOptions();

        $html = $songbirdContext->renderServerProvidedHiddenInputs(
            $cartOrderDetails
        );

        $this->assertEquals( $html, 'HTML' );
    }

    public function testSongbirdContextRenderScriptBlock() {
        $logger = $this->getLoggerMock();

        $mocks = $this->getSongbirdMocks();

        $cartOrderDetails = $mocks->cartOrderDetails;

        $mocks->songbirdCartScriptRenderer->expects( $this->once() )
            ->method('renderSongbirdScriptBlock')
            ->with(
                $this->callback(function($arg_songbirdContext) {
                    return true;
                })/*,
                $this->callback(function($arg_cartOrderDetails) use ($cartOrderDetails) {
                    return $arg_cartOrderDetails === $cartOrderDetails;
                })*/
            )
            ->will( $this->returnValue( "SCRIPT" ));

        $songbirdContext = $this->createSongbirdContext(
            $logger,
            $mocks->cartSettings,
            $mocks->songbirdRenderer,
            $mocks->songbirdCartScriptRenderer,
            $mocks->serverJWTCreator,
            $mocks->cartPaymentDetailsPage
        );

        $config = $songbirdContext->getConfigureOptions();

        $html = $songbirdContext->renderSongbirdScriptBlock(
            $cartOrderDetails
        );

        $this->assertEquals( $html, 'SCRIPT' );
    }
}