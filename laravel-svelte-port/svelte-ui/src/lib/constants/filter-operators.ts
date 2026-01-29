/**
 * Filter operators matching Vue operators.js exactly
 * Source: c:\projects\chatwoot\app\javascript\dashboard\components-next\filter\operators.js
 */

export const FILTER_OPS = {
    EQUAL_TO: 'equal_to',
    NOT_EQUAL_TO: 'not_equal_to',
    IS_PRESENT: 'is_present',
    IS_NOT_PRESENT: 'is_not_present',
    CONTAINS: 'contains',
    DOES_NOT_CONTAIN: 'does_not_contain',
    IS_GREATER_THAN: 'is_greater_than',
    IS_LESS_THAN: 'is_less_than',
    DAYS_BEFORE: 'days_before',
    STARTS_WITH: 'starts_with',
} as const;

export type FilterOpValue = (typeof FILTER_OPS)[keyof typeof FILTER_OPS];

// Operators that do NOT require input
export const NO_INPUT_OPS: FilterOpValue[] = [
    FILTER_OPS.IS_PRESENT,
    FILTER_OPS.IS_NOT_PRESENT,
];

// Operators with input type override
export const OPS_INPUT_OVERRIDE: Partial<Record<FilterOpValue, string>> = {
    [FILTER_OPS.DAYS_BEFORE]: 'plainText',
};

// Operator icons (matching Vue filterOperatorIcon)
export const OPERATOR_ICONS: Record<FilterOpValue, string> = {
    [FILTER_OPS.EQUAL_TO]: 'i-ph-equals-bold',
    [FILTER_OPS.NOT_EQUAL_TO]: 'i-ph-not-equals-bold',
    [FILTER_OPS.IS_PRESENT]: 'i-ph-member-of-bold',
    [FILTER_OPS.IS_NOT_PRESENT]: 'i-ph-not-member-of-bold',
    [FILTER_OPS.CONTAINS]: 'i-ph-superset-of-bold',
    [FILTER_OPS.DOES_NOT_CONTAIN]: 'i-ph-not-superset-of-bold',
    [FILTER_OPS.IS_GREATER_THAN]: 'i-ph-greater-than-bold',
    [FILTER_OPS.IS_LESS_THAN]: 'i-ph-less-than-bold',
    [FILTER_OPS.DAYS_BEFORE]: 'i-ph-calendar-minus-bold',
    [FILTER_OPS.STARTS_WITH]: 'i-ph-caret-line-right-bold',
};

export interface FilterOperator {
    value: FilterOpValue;
    label: string;
    icon: string;
    hasInput: boolean;
    inputOverride?: string | null;
}

/**
 * EXACT operator labels from Vue translations
 * Source: advancedFilters.json FILTER.OPERATOR_LABELS
 */
export const OPERATOR_LABELS: Record<FilterOpValue, string> = {
    [FILTER_OPS.EQUAL_TO]: 'Equal to',
    [FILTER_OPS.NOT_EQUAL_TO]: 'Not equal to',
    [FILTER_OPS.IS_PRESENT]: 'Is present',
    [FILTER_OPS.IS_NOT_PRESENT]: 'Is not present',
    [FILTER_OPS.CONTAINS]: 'Contains',
    [FILTER_OPS.DOES_NOT_CONTAIN]: 'Does not contain',
    [FILTER_OPS.IS_GREATER_THAN]: 'Is greater than',
    [FILTER_OPS.IS_LESS_THAN]: 'Is lesser than', // NOTE: Vue uses "lesser" not "less"
    [FILTER_OPS.DAYS_BEFORE]: 'Is x days before',
    [FILTER_OPS.STARTS_WITH]: 'Starts with',
};

// Build operator object
function buildOperator(value: FilterOpValue): FilterOperator {
    return {
        value,
        label: OPERATOR_LABELS[value],
        icon: OPERATOR_ICONS[value],
        hasInput: !NO_INPUT_OPS.includes(value),
        inputOverride: OPS_INPUT_OVERRIDE[value] ?? null,
    };
}

// Pre-built operator sets (matching Vue composable returns)
export const equalityOperators: FilterOperator[] = [
    buildOperator(FILTER_OPS.EQUAL_TO),
    buildOperator(FILTER_OPS.NOT_EQUAL_TO),
];

export const presenceOperators: FilterOperator[] = [
    buildOperator(FILTER_OPS.EQUAL_TO),
    buildOperator(FILTER_OPS.NOT_EQUAL_TO),
    buildOperator(FILTER_OPS.IS_PRESENT),
    buildOperator(FILTER_OPS.IS_NOT_PRESENT),
];

export const containmentOperators: FilterOperator[] = [
    buildOperator(FILTER_OPS.EQUAL_TO),
    buildOperator(FILTER_OPS.NOT_EQUAL_TO),
    buildOperator(FILTER_OPS.CONTAINS),
    buildOperator(FILTER_OPS.DOES_NOT_CONTAIN),
];

export const comparisonOperators: FilterOperator[] = [
    buildOperator(FILTER_OPS.EQUAL_TO),
    buildOperator(FILTER_OPS.NOT_EQUAL_TO),
    buildOperator(FILTER_OPS.IS_PRESENT),
    buildOperator(FILTER_OPS.IS_NOT_PRESENT),
    buildOperator(FILTER_OPS.IS_GREATER_THAN),
    buildOperator(FILTER_OPS.IS_LESS_THAN),
];

export const dateOperators: FilterOperator[] = [
    buildOperator(FILTER_OPS.IS_GREATER_THAN),
    buildOperator(FILTER_OPS.IS_LESS_THAN),
    buildOperator(FILTER_OPS.DAYS_BEFORE),
];

/**
 * Get operators by field type (matching Vue getOperatorTypes)
 */
export function getOperatorsByType(type: string): FilterOperator[] {
    switch (type) {
        case 'list':
            return equalityOperators;
        case 'text':
            return containmentOperators;
        case 'number':
            return equalityOperators;
        case 'link':
            return equalityOperators;
        case 'date':
            return comparisonOperators;
        case 'checkbox':
            return equalityOperators;
        default:
            return equalityOperators;
    }
}
