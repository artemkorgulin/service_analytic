/* eslint-disable no-unused-vars,no-eq-null*/
import { ref, computed } from '@nuxtjs/composition-api';
import equal from 'fast-deep-equal/es6';
const not = val => !val;
export function useField(field, form) {
    if (!field) {
        console.warn('useField field is required ', field);
        return;
    }
    const $externalError = ref(null);
    const validators = {
        external() {
            if ($externalError.value == null) {
                return true;
            }
            return false;
        },
        ...(field?.validators || {}),
    };
    const errorMessages = { external: () => $externalError.value, ...(field?.errorMessages || {}) };
    const $dirty = ref(false);
    const $model = ref(field.value);
    const $errors = computed(() => {
        const returnVal = {};
        for (const name of Object.keys(validators)) {
            const isValid = validators[name]($model.value, field, form);
            returnVal[name] = not(isValid);
        }
        return returnVal;
    });
    const $invalid = computed(() =>
        Object.entries($errors.value).reduce((acc, val) => {
            if (val[1]) {
                acc = true;
            }
            return acc;
        }, false)
    );
    const $errorMessage = computed(() => {
        if (!$dirty.value || !$invalid.value) {
            return '';
        }
        return Object.entries($errors.value).reduce((acc, val) => {
            if (acc) {
                return acc;
            }
            if (val[1]) {
                if (typeof errorMessages[val[0]] === 'function') {
                    acc = errorMessages[val[0]]();
                } else {
                    acc = errorMessages[val[0]];
                }
            }
            return acc;
        }, '');
    });
    const $changed = computed(() => !equal($model.value, field.value));
    return {
        $model,
        $invalid,
        $dirty,
        $changed,
        $errors,
        $errorMessage,
        $externalError,
        $__validators: field,
        $change: val => {
            $model.value = val;
        },
        $touch: () => {
            $dirty.value = true;
        },
        $reset: () => {
            $dirty.value = false;
        },
        $resetExtError: val => {
            $externalError.value = null;
        },
        $setExtError: val => {
            $externalError.value = val;
        },
        $setInitialValue: () => {
            $model.value = field.value;
        },
        $touchAndResetExtErr: () => {
            $externalError.value = null;
            $dirty.value = true;
        },
    };
}
