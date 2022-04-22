<template>
  <div :ref="setEl" id="price-slider" class="price-slider"></div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import noUiSlider from 'nouislider';

export default defineComponent({
  name: 'PriceRangeSlider',
  emits: ['change'],
  props: {
    options: {
      type: Object as PropType<{ min: number; max: number }>,
      required: true,
    },
  },
  data() {
    return {
      selectedMin: this.options.min,
      selectedMax: this.options.max,
      el: undefined as HTMLDivElement | undefined,
      numberFormatter: {
        from: (value: string): number => {
          return Number(value.replace('€ ', ''));
        },
        to: (value: number): string => {
          return `€ ${value.toFixed(0)}`;
        },
      },
    };
  },
  methods: {
    setEl(el: any): void {
      this.el = el;
    },
    updateSliderOptions(): void {
      this.selectedMin = this.options.min;
      this.selectedMax = this.options.max;
      // @ts-ignore
      this.el.noUiSlider.updateOptions(
        {
          start: [this.selectedMin, this.selectedMax],
          range: {
            min: this.options.min,
            max: this.options.max,
          },
        },
        false,
      );
    },
  },
  watch: {
    options() {
      this.updateSliderOptions();
    },
  },
  mounted() {
    if (this.el === undefined) {
      console.error('Failed to render price slider');
      return;
    }

    noUiSlider.create(this.el, {
      start: [this.selectedMin, this.selectedMax],
      connect: true,
      range: {
        min: this.options.min,
        max: this.options.max,
      },
      margin: 1,
      step: 1,
      tooltips: true,
      format: this.numberFormatter,
    });

    // @ts-ignore
    this.el.noUiSlider.on('set', (values: string[]) => {
      this.selectedMin = this.numberFormatter.from(values[0]);
      this.selectedMax = this.numberFormatter.from(values[1]);
      this.$emit('change', [this.selectedMin, this.selectedMax]);
    });
  },
});
</script>
