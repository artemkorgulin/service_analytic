<template>
    <div class="note-item" :class="type">
        <SvgIcon v-if="type === 'warning'" name="filled/warningoutlined" />
        <!-- <template v-if="type === 'warning'">
            <img src="/images/icons/note-marn-icon.svg" />
        </template> -->
        <span>{{ text }}</span>
    </div>
</template>

<script>
    export default {
        name: 'NoteItem',
        props: {
            text: {
                type: String,
                required: true,
            },
            type: {
                type: String,
                required: true,
            },
        },
    };
</script>
