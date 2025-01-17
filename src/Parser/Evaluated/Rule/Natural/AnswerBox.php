<?php
/**
 * @license see LICENSE
 */

namespace Serps\SearchEngine\Google\Parser\Evaluated\Rule\Natural;

use Serps\SearchEngine\Google\Page\GoogleDom;
use Serps\Core\Serp\BaseResult;
use Serps\Core\Serp\IndexedResultSet;
use Serps\SearchEngine\Google\Parser\ParsingRuleInterface;
use Serps\SearchEngine\Google\NaturalResultType;

class AnswerBox implements ParsingRuleInterface
{

    public function match(GoogleDom $dom, \Serps\Core\Dom\DomElement $node)
    {
        if ((!$node->hasClasses(['kno-kp']) && $dom->cssQuery('.g.mnr-c.g-blk', $node)->length)
            && (
                $dom->cssQuery('.ifM9O > .bNg8Rb', $node)->length == 1 ||
                $dom->cssQuery('._Z7', $node)->length == 1      // TODO used for BC, remove in the future
            )
        ) {
            return self::RULE_MATCH_MATCHED;
        }
        return self::RULE_MATCH_NOMATCH;
    }

    protected function parseNode(GoogleDom $dom, \DOMElement $node)
    {
        return [
            'title'   => function () use ($dom, $node) {
                $titleTag = $dom->cssQuery('.rc .r h3', $node)
                    ->item(0);
                if (!$titleTag) {
                    return;
                }
                return $titleTag->nodeValue;
            },
            'url'     => function () use ($dom, $node) {
                $aTag = $dom->cssQuery('.rc .r a', $node)
                    ->item(0);
                if (!$aTag) {
                    // TODO ERROR
                    return;
                }
                return $dom->getUrl()->resolveAsString($aTag->getAttribute('href'));
            },
            'destination' => function () use ($dom, $node) {
                $citeTag = $dom->cssQuery('.rc .r cite', $node)
                    ->item(0);
                if (!$citeTag) {
                    // TODO ERROR
                    return;
                }
                return $citeTag->nodeValue;
            },
            'description' => function () use ($dom, $node) {
                // TODO "mod ._Tgc" kept for BC, remove in the future
                $citeTag = $dom->cssQuery('.mod .LGOjhe, .mod .Y0NH2b, .mod .Crs1tb', $node)
                    ->item(0);
                if (!$citeTag) {
                    // TODO ERROR
                    return;
                }
                return $citeTag->nodeValue;
            },
        ];
    }

    public function parse(GoogleDom $dom, \DOMElement $node, IndexedResultSet $resultSet)
    {
        $item = new BaseResult(
            [NaturalResultType::ANSWER_BOX],
            $this->parseNode($dom, $node)
        );
        $resultSet->addItem($item);
    }
}
