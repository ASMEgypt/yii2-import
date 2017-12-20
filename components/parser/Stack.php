<?php
/**
 * User: execut
 * Date: 26.07.16
 * Time: 15:43
 */

namespace execut\import\components\parser;


use execut\import\components\Parser;
use yii\base\Component;

class Stack extends Component
{
    /**
     * @var Parser[]
     */
    protected $parsers = [];
    public $relations = [];
    public $stacks = [];
    public function setParsers($parsers) {
        foreach ($parsers as $key => $parser) {
            if (is_array($parser)) {
                $parser = new Parser($parser);
            }

            $parser->stack = $this;
            $parsers[$key] = $parser;
        }

        $this->parsers = $parsers;
    }

    public function parse() {
        $this->results = [];
        foreach (array_keys($this->parsers) as $parserKey) {
            $this->parseModel($parserKey);
        }

        $result = $this->results;

        return $result;
    }

    protected $results = [];
    public function parseModel($parserKey) {
        if (isset($this->results[$parserKey])) {
            return $this->results[$parserKey];
        }

        $relations = [];
        if (!empty($this->relations[$parserKey])) {
            foreach ($this->relations[$parserKey] as $relationAttribute => $relationAdapter) {
                if (is_int($relationAttribute)) {
                    $relationAttribute = $relationAdapter;
                }

                $relationModel = $this->parseModel($relationAdapter);
                $relations[$relationAttribute] = $relationModel->id;
            }
        }

        $parser = $this->parsers[$parserKey];
        $parserAttributes = array_merge($relations, $parser->attributes);
        $parser->attributes = $parserAttributes;
        $result = $parser->parse();
        $model = $result->getModel();
        foreach ($relations as $relation => $value) {
            $model->$relation = $value;
        }

        if ($model->isNewRecord || $parser->modelsFinder->isUpdateAlways) {
            $model->save(false);
        }

        return $this->results[$parserKey] = $model;
    }

    public function setRow($row) {
        foreach ($this->parsers as $parser) {
            $parser->row = $row;
        }
    }
}