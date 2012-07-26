<?php

/**
 * PHP Sass tests.
 * @group sass
 */
class PHPSass_TestCase extends PHPUnit_Framework_TestCase {

  /**
   * This is the path to a directory of SASS, SCSS and CSS files used in tests.
   */
  var $css_tests_path;

  /**
   * This is the location of the PHPSass library being used.
   */
  var $phpsass_library_path;

  protected function setUp() {
    parent::setUp();

    $this->requirePHPSassLibrary();
    $this->css_tests_path = dirname(__FILE__);
  }

  /**
   * Require the PHPSass Library.
   *
   * We try to include it from the local site if it's around, otherwise we try a
   * few known locations, and then failing all of that we fall back to
   * downloading it from the web.
   */
  protected function requirePHPSassLibrary() {

    // Allow people to specify the library before we are called.
    if (isset($this->phpsass_library_path)) {

    }
    // Try to use libraries first.
    elseif (($library_path = dirname(__FILE__) . '/..') && file_exists($library_path . '/SassParser.php')) {
      $this->phpsass_library_path = $library_path;
    }

    if (isset($this->phpsass_library_path)) {
      require_once($this->phpsass_library_path . '/SassParser.php');
    }
    else {
      throw new Exception('Could not find PHPSass compiler.');
    }
  }

  protected function runSassTest($input, $output = FALSE, $settings = array()) {
    $name = $input;

    $path = $this->css_tests_path;
    $output = $path . '/' . ($output ? $output : preg_replace('/\..+$/', '.css', $input));
    $input = $path . '/' . $input;

    if (!file_exists($input)) {
      return $this->fail('Input file not found - ' . $input);
    }
    if (!file_exists($output)) {
      return $this->fail('Comparison file not found - ' . $output);
    }

    try {
      $settings = $settings + array(
        'style' => 'nested',
        'cache' => FALSE,
        'syntax' => array_pop(explode('.', $input)),
        'debug' => FALSE,
        'debug_info' => FALSE,
        'callbacks' => array(
          'debug' => array($this, 'sassParserDebug'),
          'warn' => array($this, 'sassParserWarning'),
        ),
      );
      $parser = new SassParser($settings);
      $result = $parser->toCss($input);
    }
    catch (Exception $e) {
      $this->fail(t('Exception occured when compiling file') . ': ' . ((string) $e));
    }

    $compare = file_get_contents($output);
    if ($compare === FALSE) {
      $this->fail('Unable to load comparison file - ' . $compare);
    }

    $_result = $this->trimResult($result);
    $_compare = $this->trimResult($compare);

    $this->assertEquals($_result, $_compare, 'Result for ' . $name . ' did not match comparison file');
  }

  /**
   * Logging callback for PHPSass debug messages.
   */
  public function sassParserDebug($message, $context) {

  }

  /**
   * Logging callback for PHPSass warning messages.
   */
  public function sassParserWarning($message, $context) {

  }

  protected function trimResult(&$input) {
    $trim = preg_replace('/[\s;]+/', '', $input);
    $trim = preg_replace('/\/\*.+?\*\//m', '', $trim);
    return $trim;
  }

  public function testAlt() {
    $this->runSassTest('alt.sass');
    $this->runSassTest('alt.scss');
  }
}
