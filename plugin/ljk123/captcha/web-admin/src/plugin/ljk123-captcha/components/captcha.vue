<template>
    <div v-if="visible">
        <angle v-if="type === 0" :visible="visible" @close="close" v-bind="$attrs">
            <!-- 子组件插槽暴露出去 -->
            <template v-for="(_, key) in $slots" #[key]="slotProps" :key="key">
                <slot :name="key" v-bind="slotProps"></slot>
            </template>
        </angle>     
        <slider v-if="type === 1" :visible="visible" @close="close" v-bind="$attrs">
            <!-- 子组件插槽暴露出去 -->
            <template v-for="(_, key) in $slots" #[key]="slotProps" :key="key">
                <slot :name="key" v-bind="slotProps"></slot>
            </template>
        </slider>        
    </div>
</template>

<script setup>

import Angle from "./angle.vue"
import Slider from "./slider.vue"


const emit = defineEmits(["close"]);

const close = (option) => {
    emit('close', option);
}
const props = defineProps({
    visible: {
        type: Boolean,
        default: true,
    },
    type: {
        type: Number,
        default: 1
    },
})

</script>
<style scoped>
</style>