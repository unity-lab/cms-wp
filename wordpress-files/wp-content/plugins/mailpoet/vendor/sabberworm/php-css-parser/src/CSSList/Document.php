<?php
namespace Sabberworm\CSS\CSSList;
if (!defined('ABSPATH')) exit;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\RuleSet\RuleSet;
class Document extends CSSBlockList
{
 public function __construct($iLineNo = 0)
 {
 parent::__construct($iLineNo);
 }
 public static function parse(ParserState $oParserState)
 {
 $oDocument = new Document($oParserState->currentLine());
 CSSList::parseList($oParserState, $oDocument);
 return $oDocument;
 }
 public function getAllDeclarationBlocks()
 {
 $aResult = [];
 $this->allDeclarationBlocks($aResult);
 return $aResult;
 }
 public function getAllSelectors()
 {
 return $this->getAllDeclarationBlocks();
 }
 public function getAllRuleSets()
 {
 $aResult = [];
 $this->allRuleSets($aResult);
 return $aResult;
 }
 public function getSelectorsBySpecificity($sSpecificitySearch = null)
 {
 $aResult = [];
 $this->allSelectors($aResult, $sSpecificitySearch);
 return $aResult;
 }
 public function expandShorthands()
 {
 foreach ($this->getAllDeclarationBlocks() as $oDeclaration) {
 $oDeclaration->expandShorthands();
 }
 }
 public function createShorthands()
 {
 foreach ($this->getAllDeclarationBlocks() as $oDeclaration) {
 $oDeclaration->createShorthands();
 }
 }
 public function render($oOutputFormat = null)
 {
 if ($oOutputFormat === null) {
 $oOutputFormat = new OutputFormat();
 }
 return $oOutputFormat->comments($this) . $this->renderListContents($oOutputFormat);
 }
 public function isRootList()
 {
 return true;
 }
}
