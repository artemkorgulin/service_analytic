import { ref, computed, onMounted } from '@nuxtjs/composition-api';

export function useListFixHeight(props) {
    const wrapper = ref(null);
    const innerHeight = ref('100%');
    const styles = computed(() => ({ height: innerHeight.value + 'px' }));
    const componentData = computed(() => ({
        is: props.items.length > 3 ? 'PerfectScrollbar' : 'div',
    }));

    const setScrollAreaHeight = () => {
        if (!wrapper?.value) {
            return;
        }
        innerHeight.value = wrapper.value?.offsetHeight;
    };
    onMounted(() => setScrollAreaHeight());

    return {
        wrapper,

        innerHeight,
        styles,
        componentData,

        setScrollAreaHeight,
    };
}
