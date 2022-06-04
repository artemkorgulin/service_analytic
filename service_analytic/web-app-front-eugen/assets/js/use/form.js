/* eslint-disable no-unused-vars */
import { computed, ref, watch, unref } from '@nuxtjs/composition-api';
import { useField } from '~use/field';

function getFlag(formObject, flagValue) {
    let result = false;
    for (const field in formObject) {
        if (formObject[field][flagValue].value) {
            result = true;
            break;
        }
    }
    return result;
}
export function useForm(form, fields) {
    const $key = ref(2);

    if (fields) {
        const formFields = unref(fields);
        watch(
            () => Object.keys(formFields),
            (newFormFields, oldFormFields) => {
                for (const formField of oldFormFields) {
                    if (!newFormFields.includes(formField)) {
                        delete form[formField];
                    }
                }

                for (const formField of newFormFields) {
                    if (!oldFormFields.includes(formField)) {
                        form[formField] = useField(formFields[formField], form);
                    }
                }
                $key.value += 1;
            }
        );
    }

    // const formMeta = reactive({
    //     model: computed(() => Object.entries(form).reduce((acc, [key, val]) => {
    //         acc[key] = val.$model;
    //         return acc;
    //     }, {})),
    //     pending: false,
    //     invalid: computed(() => getFlag(form, '$invalid')),
    //     dirty: computed(() => getFlag(form, '$dirty')),
    //     changed: computed(() => getFlag(form, '$changed')),
    // });
    const $setExtErrors = payload => {
        if (typeof payload !== 'object' || Object.keys(payload).length === 0) {
            console.warn('use form setExtErrors no err');
            return;
        }
        for (const key of Object.keys(payload)) {
            form[key].$setExtError(payload[key]);
        }
    };
    const $touch = () => {
        for (const key in form) {
            form[key].$touch();
        }
    };
    const $reset = () => {
        for (const key in form) {
            form[key].$reset();
        }
    };
    const $setInitialValue = () => {
        for (const key in form) {
            form[key].$setInitialValue();
        }
    };
    return {
        $model: computed(() =>
            Object.entries(form).reduce((acc, [key, val]) => {
                acc[key] = val.$model;
                return acc;
            }, {})
        ),
        $invalid: computed(() => getFlag(form, '$invalid')),
        $dirty: computed(() => getFlag(form, '$dirty')),
        $changed: computed(() => getFlag(form, '$changed')),
        $key,
        form,
        $setExtErrors,
        $touch,
        $reset,
        $setInitialValue,
    };
}
