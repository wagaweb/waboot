<template>
  <div :ref="setEl" id="price-slider" class="price-slider"></div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import noUiSlider from 'nouislider';

export default defineComponent({
  name: 'PriceRangeSlider',
  emits: ['change'],
  props: {
    min: { type: Number, required: true },
    max: { type: Number, required: true },
    selectedMin: { type: Number, default: 0 },
    selectedMax: { type: Number, default: 0 },
  },
  data() {
    return {
      selMin: this.selectedMin,
      selMax: this.selectedMax,
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
    updateRange(): void {
      // @ts-ignore
      this.el.noUiSlider.updateOptions(
        {
          start: [this.selectedMin, this.selectedMax],
          range: {
            min: this.min,
            max: this.max,
          },
        },
        false,
      );
    },
    updateSelectedRange(): void {
      this.selMin = this.selectedMin;
      this.selMax = this.selectedMax;
      // @ts-ignore
      this.el.noUiSlider.set([this.selMin, this.selMax], false);
    },
  },
  watch: {
    min() {
      this.updateRange();
    },
    max() {
      this.updateRange();
    },
    selectedMin() {
      this.updateSelectedRange();
    },
    selectedMax() {
      this.updateSelectedRange();
    },
  },
  mounted() {
    if (this.el === undefined) {
      console.error('Failed to render price slider');
      return;
    }

    noUiSlider.create(this.el, {
      start: [this.selMin, this.selMax],
      connect: true,
      range: {
        min: this.min,
        max: this.max,
      },
      margin: 1,
      step: 1,
      tooltips: true,
      format: this.numberFormatter,
    });

    // @ts-ignore
    this.el.noUiSlider.on('set', (values: string[]) => {
      this.selMin = this.numberFormatter.from(values[0]);
      this.selMax = this.numberFormatter.from(values[1]);
      this.$emit('change', [this.selMin, this.selMax]);
    });
  },
});
</script>
