<?php
namespace HMinng\DBLibrary\AtsValidator;

use HMinng\DBLibrary\AtsValidator\AtsValidation;

class AtsDaoValidation
{
    protected $value    = NULL;
    protected $required = FALSE;

    const DAO_TABLE   = 1;
    const DAO_PRIMARY = 2;
    const DAO_FIELD   = 3;
    const DAO_WHERE   = 4;
    const DAO_LIMIT   = 5;
    const DAO_HAVING  = 6;
    const DAO_OFFSET  = 7;

    const DAO_TABLE_NOT_EXISTS             = 101;
    const DAO_PRIMARY_PARAM_WRONG          = 201;
    const DAO_FIELD_NOT_ARRAY              = 301;
    const DAO_FIELD_IS_EMPTY               = 302;
    const DAO_WHERE_AND_HAVING_UNDEFINED   = 401;
    const DAO_WHERE_AND_HAVING_PARAM_WRONG = 402;
    const DAO_LIMIT_AND_OFFSET_PARAM_WRONG = 501;

    public function __construct($params)
    {
        $this->validate($params);
    }

    protected function validationTable()
    {
        if ($this->required) {
            if (! AtsValidation::hasTable($this->value)) {
                return self::DAO_TABLE_NOT_EXISTS;
            }
        }
    }

    protected function validationField()
    {
        if ($this->required) {
            if (! is_array($this->value)) {
                return self::DAO_FIELD_NOT_ARRAY;
            } elseif (! $this->value) {
                return self::DAO_FIELD_IS_EMPTY;
            }
        } else {
            if ($this->value && ! is_array($this->value)) {
                return self::DAO_FIELD_NOT_ARRAY;
            }
        }
    }

    protected function validationWhereAndHaving()
    {
        if ($this->required) {
            if (! isset($this->value['value']) || ! isset($this->value['key'])) {
                return self::DAO_WHERE_AND_HAVING_UNDEFINED;
            } elseif (substr_count($this->value['key'], '?') && count($this->value['value']) > substr_count($this->value['key'], '?')) {
                return self::DAO_WHERE_AND_HAVING_PARAM_WRONG;
            }
        } else {
            if (isset($this->value['value']) && isset($this->value['key'])) {
                if (substr_count($this->value['key'], '?') && count($this->value['value']) > substr_count($this->value['key'], '?')) {
                    return self::DAO_WHERE_AND_HAVING_PARAM_WRONG;
                }
            }
        }
    }

    protected function validationLimitAndOffset()
    {
        if ($this->required) {
            if (! $this->value || ! is_numeric($this->value)) {
                return self::DAO_LIMIT_AND_OFFSET_PARAM_WRONG;
            }
        } else {
            if ($this->value && ! is_numeric($this->value)) {
                return self::DAO_LIMIT_AND_OFFSET_PARAM_WRONG;
            }
        }
    }

    private function validate($params)
    {
        foreach ($params as $type => $param) {
            $this->value    = isset($param['value']) ? $param['value'] : NULL;
            $this->required = isset($param['required']) ? $param['required'] : FALSE;

            switch ($type)
            {
                case self::DAO_TABLE:
                    if (self::DAO_TABLE_NOT_EXISTS == $this->validationTable()) {
                        throw new \Exception("DAO 层出错：Table ('$this->value') 不存在");
                    }
                    break;
                case self::DAO_PRIMARY:
                    if (self::DAO_PRIMARY_PARAM_WRONG == $this->validationPrimary()) {
                        throw new \Exception("DAO 层出错：Primary ('$this->value') 不合法");
                    }
                    break;
                case self::DAO_FIELD:
                    if (self::DAO_FIELD_NOT_ARRAY == $this->validationField()) {
                        throw new \Exception("DAO 层出错：Field 不是数组");
                    } elseif (self::DAO_FIELD_IS_EMPTY == $this->validationField()) {
                        throw new \Exception("DAO 层出错：Field 数组为空");
                    }
                    break;
                case self::DAO_WHERE:
                    if (self::DAO_WHERE_AND_HAVING_UNDEFINED == $this->validationWhereAndHaving()) {
                        throw new \Exception("DAO 层出错：Where 条件参数未定义");
                    } elseif (self::DAO_WHERE_AND_HAVING_PARAM_WRONG == $this->validationWhereAndHaving()) {
                        throw new \Exception("DAO 层出错：Where 条件参数不合法");
                    }
                    break;
                case self::DAO_LIMIT:
                    if (self::DAO_LIMIT_AND_OFFSET_PARAM_WRONG == $this->validationLimitAndOffset()) {
                        throw new \Exception("DAO 层出错：Limit ('$this->value') 值不合法");
                    }
                    break;
                case self::DAO_HAVING:
                    if (self::DAO_WHERE_AND_HAVING_UNDEFINED == $this->validationWhereAndHaving()) {
                        throw new \Exception("DAO 层出错：Where 条件参数未定义");
                    } elseif (self::DAO_WHERE_AND_HAVING_PARAM_WRONG == $this->validationWhereAndHaving()) {
                        throw new \Exception("DAO 层出错：Where 条件参数不合法");
                    }
                    break;
                case self::DAO_OFFSET:
                    if (self::DAO_LIMIT_AND_OFFSET_PARAM_WRONG == $this->validationLimitAndOffset()) {
                        throw new \Exception("DAO 层出错：Offset ('$this->value') 值不合法");
                    }
                    break;
            }
        }
    }
}