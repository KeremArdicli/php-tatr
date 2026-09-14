<?php

declare(strict_types=1);

namespace PhpTatr\Query;

enum OpKind: string
{
    case ANY = 'ANY';
    case TAG = 'TAG';
    case NOT = 'NOT';
    case OR = 'OR';
    case AND = 'AND';
    case TAGGED = 'TAGGED';
    case ID = 'ID';
    case PRIORITY = 'PRIORITY';
    case INTEGER = 'INTEGER';
    case LT = 'LT';
    case GT = 'GT';
    case LTE = 'LTE';
    case GTE = 'GTE';
    case EQ = 'EQ';
    case NEQ = 'NEQ';
}
