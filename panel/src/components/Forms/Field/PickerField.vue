<template>
  <k-field v-bind="$props" :class="`k-picker-field k-${type}-field`">
    <!-- Options dropdown -->
    <k-button-group
      v-if="!disabled && dropdown.options.length > 0"
      class="k-field-options"
    >
      <k-options-dropdown ref="options" v-bind="dropdown" @action="onOption" />
    </k-button-group>

    <k-dropzone :disabled="!hasDropzone" @drop="onDrop">
      <!-- Items list -->
      <k-items
        v-if="value.length"
        :data-loading="isLoading"
        :items="items"
        :layout="layout"
        :link="link"
        :size="size"
        :sortable="!disabled && value.length > 1"
        @sort="onInput"
        @sortChange="$emit('change', $event)"
      >
        <template #options="{ index }">
          <k-button
            v-if="!disabled"
            :tooltip="$t('remove')"
            icon="remove"
            @click="remove(index)"
          />
        </template>
      </k-items>

      <!-- Empty state -->
      <k-empty
        v-else
        :layout="layout"
        :data-invalid="isInvalid"
        :icon="icon"
        @click="prompt"
      >
        {{ empty || $t("field." + type + ".empty") }}
      </k-empty>
    </k-dropzone>

    <!-- Additional dialogs etc. -->
    <k-upload v-if="hasUploads" ref="upload" @success="onUpload" />
  </k-field>
</template>

<script>
import { props as Field } from "@/components/Forms/Field.vue";

export default {
  mixins: [Field],
  inheritAttrs: false,
  props: {
    /**
     * Text to display on empty state
     */
    empty: String,
    /**
     * Icon to display on empty state
     */
    icon: String,
    /**
     * @todo what is this?
     */
    info: String,
    /**
     * Whether to link items
     */
    link: Boolean,
    /**
     * Switches the layout of the items
     * @values list, cards
     */
    layout: {
      type: String,
      default: "list"
    },
    /**
     * Maximum number of items
     */
    max: Number,
    /**
     * Minimum number of items
     */
    min: Number,
    /**
     * If false, only a single item can be selected
     */
    multiple: Boolean,
    /**
     * @todo what is this?
     */
    parent: String,
    /**
     * Whether to show search bar in dialog
     */
    search: Boolean,
    /**
     * Size of picker dialog
     */
    size: String,
    /**
     * @todo what is this?
     */
    text: String,
    value: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  data() {
    return {
      items: {},
      isLoading: true
    };
  },
  computed: {
    /**
     * Whether more items can be added
     * @returns {Boolean}
     */
    canAddMore() {
      // only allow one, and already have one
      if (!this.multiple && this.value >= 1) {
        return false;
      }

      // only allow X, and already have X
      if (this.max && this.max <= this.value.length) {
        return false;
      }

      return true;
    },
    /**
     * Returns prop for options dropdown
     * @returns {Object}
     */
    dropdown() {
      return {
        options: [
          {
            icon: this.canAddMore ? "add" : "refresh",
            text: this.canAddMore ? this.$t("add") : this.$t("change"),
            option: "picker"
          }
        ]
      };
    },
    /**
     * Whether to add dropzone component
     * @returns {Boolean}
     */
    hasDropzone() {
      return false;
    },
    /**
     * Whether to add upload component
     * @returns {Boolean}
     */
    hasUploads() {
      return false;
    },
    /**
     * Whether field value is invalid
     * @returns {Boolean}
     */
    isInvalid() {
      if (this.required && this.value.length === 0) {
        return true;
      }

      if (this.min && this.value.length < this.min) {
        return true;
      }

      if (this.max && this.value.length > this.max) {
        return true;
      }

      return false;
    }
  },
  watch: {
    value: {
      handler() {
        this.fetch();
      },
      immediate: true
    }
  },
  methods: {
    /**
     * Fetches info from API for each
     * value entry to display as item
     */
    async fetch() {
      // set loading status
      this.isLoading = true;

      // gather item display data for each value entry
      let items = {};
      let fetch = [];

      for (const id in this.value) {
        if (id in this.items) {
          // reuse entries that already had been fetched
          items[id] = this.items[id];
        } else {
          // add new entries to list for collective API call
          fetch.push(id);
        }
      }

      // get data for all new entries from API
      fetch = await this.$api.get("picker/" + this.type, { ids: fetch });

      // update items data with old and new entry data
      this.items = { ...items, ...fetch };

      // reset loading status
      this.isLoading = false;
    },
    /**
     * Focusses the field
     * @todo
     */
    focus() {},
    /**
     * Handles dropzone event
     */
    onDrop() {},
    /**
     * Handles options dropdown actions
     */
    onOption(option) {
      switch (option) {
        case "picker":
          return this.open();
        case "upload":
          return this.$refs.upload.open(this.uploadParams);
      }
    },
    /**
     * Handles uploaded files
     * @param {Object} upload
     * @param {array} files
     */
    onUpload(upload, files) {
      let value = this.value;

      if (this.multiple === false) {
        value = [];
      }

      for (const file in files) {
        if (value.includes(file.id)) {
          value.push(file.id);
        }
      }

      this.$emit("input", value);
      this.$events.$emit("model.update");
    },
    /**
     * Opens the picker dialog
     */
    open() {
      if (this.disabled) {
        return false;
      }

      this.$dialog(this.type + "/picker", {
        query: {
          selected: this.value
        },
        props: {
          max: this.max,
          min: this.min,
          multiple: this.multiple,
          search: this.search,
          size: this.size
        },
        submit: ({ value }) => {
          this.$emit("input", value);
        }
      });
    },
    /**
     * Triggers action when clicking
     * on empty state
     * @param {Event} e
     */
    prompt(e) {
      e.stopPropagation();

      if (this.disabled) {
        return false;
      }

      if (this.dropdown.options.length > 1) {
        this.$refs.options.toggle();
      } else {
        this.open();
      }
    },
    /**
     * Removes item at index
     * @param {int} index
     */
    remove(index) {
      const value = this.value;
      this.$emit("input", value.splice(index, 1));
    },
    /**
     * Removes item with id
     * @param {string} id
     */
    removeById(id) {
      const value = this.value.filter((x) => x.id !== id);
      this.$emit("input", value);
    }
  }
};
</script>

<style>
.k-picker-field[data-disabled="true"] * {
  pointer-events: all !important;
}
</style>
