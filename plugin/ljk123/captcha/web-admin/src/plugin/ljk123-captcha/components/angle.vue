<template>
  <div class="overlay" v-if="visible" @click.self="close">
    <div class="popup">
      <template v-if="$slots.header">
        <slot name="header" />
      </template>
      <template v-else>
        <div>{{ $t('plugin.ljk123-captcha.angle.title') }}</div>
      </template>
      <template v-if="$slots.body">
        <slot name="body" :imageSrc="imageSrc" :imageTransform="imageTransform" />
      </template>
      <template v-else>
        <div class="image-container">
          <img v-if="imageSrc" :src="imageSrc" :style="{ transform: `rotate(${imageTransform}deg)` }" />
          <loading v-else></loading>
        </div>
      </template>
      <template v-if="$slots.msgbox">
        <slot name="msgbox" :isSuccess="isSuccess" :isError="isError" :successOption="successOption" />
      </template>
      <template v-else>
        <div class="msgbox">
          <div class="error" v-show="isError">{{ $t('plugin.ljk123-captcha.base.fail') }}</div>
          <div class="success" v-show="isSuccess">{{ $t('plugin.ljk123-captcha.base.success', successOption) }}</div>
        </div>
      </template>
      <div class="slider-container" @mousedown="onMouseDown" @touchstart="onTouchStart">
        <div class="slider-bg" :style="'width:' + draggingWidth + 'px'">
          <div class="slider-container-text"
            :style="slider?.parentElement ? 'width:' + slider.parentElement.offsetWidth + 'px' : ''">{{
    $t('plugin.ljk123-captcha.angle.sliderTxt') }}</div>
        </div>
        <div class="slider" ref="slider" :style="positionLeftStyle">
          <div class="line"></div>
        </div>
        <div class="slider-container-text slidetounlock">{{ $t('plugin.ljk123-captcha.angle.sliderTxt') }}</div>
      </div>
      <div class="helper">
        <span @click="callGetCaptcha">{{ $t('plugin.ljk123-captcha.base.refresh') }}</span>
        <span @click="onFeedback" v-if="props.feedbackUrl">{{ $t('plugin.ljk123-captcha.base.faceback') }}</span>
      </div>
    </div>
    <div class="mask"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { getCaptcha, preVerify } from "../api/captcha";
import Loading from "./loading.vue";

const CURRENT_TYPE = 0

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: true,
  },
  feedbackUrl: {
    type: String,
    default: '',
  },
});
// Emits
const emit = defineEmits(["close"]);

// State
const imageSrc = ref('');
const captchaId = ref(null);
const isError = ref(false);
const currentX = ref(0);
const maxAngle = 360;

const useTime = ref(0);

const isSuccess = ref(false);
const successOption = computed(() => ({
  sec: (useTime.value / 1000).toFixed(2),
  per: (100 - (useTime.value / 100)).toFixed(2),
}))

let startX = 0;
let dragging = false;

const slider = ref(null)


const sliderWidth = computed(() => {
  return (slider.value?.parentElement.offsetWidth || 1) - (slider.value?.offsetWidth || 0)
})
const imageTransform = computed(() => {
  return Math.max(0, Math.min((currentX.value / sliderWidth.value) * maxAngle, maxAngle));
})

const draggingWidth = computed(() => {
  return Math.max(0, Math.min(currentX.value, sliderWidth.value)) + slider.value?.offsetWidth / 2;
})
const positionLeftStyle = computed(() => {
  const draggingWidth = Math.max(0, Math.min(currentX.value, sliderWidth.value));
  return `left:${draggingWidth}px`;
})


const callGetCaptcha = () => {
  getCaptcha({
    type: CURRENT_TYPE,
    captcha_id: captchaId.value,
  }).then(res => {
    if (!res.success) {
      return;
    }
    const { data } = res;
    if (data.option.type !== CURRENT_TYPE) {
      console.warn('server response error');
      return;
    }
    captchaId.value = data.captchaId;
    imageSrc.value = data.option.img;
    currentX.value = 0;
  });
};

const callPreVerify = (option) => {
  return preVerify(captchaId.value, {
    type: CURRENT_TYPE,
    verify: {
      ...option,
      angle: imageTransform.value,
    }
  }).then(res => {
    if (!res.success) {
      isError.value = true;
      return new Promise((resolve) => {
        resolve({ validated: false })
      });
    }
    const { data } = res;
    if (data.ret) {
      return new Promise((resolve) => {
        resolve({ validated: true, key: data.data.key })
      });
    } else {
      isError.value = true;
      if (data.msg === 'captchaIdInvalid' || data.data?.expire) {
        callGetCaptcha();
        setTimeout(() => {
          if (!dragging && isError.value === true) {
            isError.value = false
          }
        }, 900)
      }
      return new Promise((resolve) => {
        resolve({ validated: false })
      });
    }
  });
};
const onFeedback = () => {
  window.open(props.feedbackUrl, '_blank');
}
let touchStart = 0
// Touch and Mouse event handlers
const onTouchStart = (event) => {
  const touch = event.touches[0];
  startDrag(touch.clientX);
  document.addEventListener('touchmove', onTouchMove);
  document.addEventListener('touchend', onTouchEnd);
};

const onMouseDown = (event) => {
  startDrag(event.clientX);
  document.addEventListener('mousemove', onMouseMove);
  document.addEventListener('mouseup', onMouseUp);
};

let touchPos = []
const startDrag = (startXValue) => {
  touchStart = (new Date).getTime()
  startX = startXValue;
  touchPos = []
  dragging = true;
  isError.value = false
};

const onTouchMove = (event) => {
  if (dragging) {
    const touch = event.touches[0];
    updatePosition(touch.clientX, touch.clientY);
  }
};

const onMouseMove = (event) => {
  if (dragging) {
    updatePosition(event.clientX, event.clientY);
  }
};

const updatePosition = (currentXValue, currentYValue) => {
  touchPos.push({
    x: currentXValue,
    y: currentYValue,
    offset: (new Date).getTime() - touchStart
  })
  currentX.value = currentXValue - startX;
};

const onTouchEnd = () => {
  endDrag();
};

const onMouseUp = () => {
  endDrag();
};

const endDrag = () => {
  if (dragging) {
    document.removeEventListener('mousemove', onMouseMove);
    document.removeEventListener('mouseup', onMouseUp);
    document.removeEventListener('touchmove', onTouchMove);
    document.removeEventListener('touchend', onTouchEnd);
    const touchEnd = (new Date).getTime()
    const touchUse = useTime.value = touchEnd - touchStart
    if (currentX.value < 0 || touchEnd - touchStart < 150 || touchPos.length < 10) {
      //操作太快的
      currentX.value = 0
      dragging = false
      return
    }
    callPreVerify({
      touchUse,
      touchPos
    }).then(({ validated, key }) => {
      if (validated) {
        isSuccess.value = true
        setTimeout(() => {
          emit('close', {
            validated: true,
            formData: {
              type: CURRENT_TYPE,
              captcha_id: captchaId.value,
              key,
            }
          });
        }, 1500);
        return
      }
      currentX.value = 0
      dragging = false
    });

  }
};

// On component mount
onMounted(() => {
  callGetCaptcha();
});

// Close the popup
const close = () => {
  if (dragging) {
    return
  }
  emit('close', {
    validated: false
  });
};

</script>

<style scoped>
.overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  font-size: 16px;
}

.popup {
  background: white;
  width: 300px;
  padding: 20px;
  border-radius: 10px;
  text-align: center;
  position: relative;
}

.image-container {
  margin: 20px 0;
  display: flex;
  justify-content: center;
}

.image-container img {
  width: 100%;
  border-radius: 50%;
  display: block;
  margin: 0 auto;
}

.slider-container {
  width: 100%;
  height: 30px;
  background: #f3f3f3;
  border-radius: 17px;
  position: relative;
}

.slider-bg {
  background-color: #7eb839;
  height: 100%;
  position: absolute;
  border-radius: 17px 0 0 17px;
  overflow: hidden;
}

.slider {
  width: 34px;
  height: 34px;
  background: #333;
  border-radius: 50%;
  position: absolute;
  top: -2px;
  left: 0;
  touch-action: none;
  /* 禁用浏览器默认行为 */
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.slider::before,
.slider::after {
  content: "";
  width: 2px;
  height: 14px;
  background: white;
  position: absolute;
}

.slider::before {
  left: 10px;
}

.slider::after {
  left: 22px;
}

.slider .line {
  width: 2px;
  height: 18px;
  background: white;
}

.slider-container-text {
  height: 30px;
  font-size: 12px;
  line-height: 30px;
  margin: auto;
  color: #fff;
}

.slidetounlock {

  color: #828282;
  background: linear-gradient(to right, #4d4d4d 0%, #4d4d4d 40%, #fff 50%, #4d4d4d 60%, #4d4d4d 100%);
  background-clip: text;
  -webkit-text-fill-color: transparent;
  animation: slidetounlock 3s infinite linear;
  -webkit-text-size-adjust: none;
}

@keyframes slidetounlock {
  0% {
    background-position: -200px 0;
  }

  100% {
    background-position: 200px 0;
  }
}


.msgbox {
  height: 30px;
}

.error {
  color: red;
  margin: 10px 0;
}

.success {
  font-size: 12px;
  color: #7eb839;
  margin: 10px 0;
}

.mask {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.5);
  display: none;
}

.helper {
  margin-top: 30px;
  font-size: 12px;
  color: #bdbdbd;
}

.helper span {
  cursor: pointer;
  margin: 0 10px;
}
</style>
