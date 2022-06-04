```
<template>
    <BaseDialog
        v-model="isShow"
    >

    </BaseDialog>
</template>

<script>
export default {
    name: 'FILENAME',
    data() {
        return {
            isShow: true,
            isLoading: false,
        };
    },
    methods: {
        async handleConfirm() {
            this.isLoading = true;
            try {
                // request here
                this.isLoading = false;
                this.isShow = false;
            } catch (error) {
                this.isLoading = false;
                this.isShow = false;
            }
        },
        handleClose() {
            this.isShow = false;
        },
    },
};
</script>

```

Open modal everywhere in app

```
this.$modal.open({
  component: 'FILENAME',
});
```
