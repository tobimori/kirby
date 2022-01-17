<template>
  <k-dialog
    ref="dialog"
    v-bind="$props"
    class="k-picker-dialog"
    @cancel="$emit('cancel')"
    @close="$emit('close')"
    @ready="$emit('ready')"
    @submit="$emit('submit', selected)"
  >
    <header v-if="parent" class="k-picker-dialog-navbar">
      <k-button
        :disabled="!parent.id"
        :tooltip="$t('back')"
        icon="angle-left"
        @click="back"
      />
      <k-headline>{{ parent.title }}</k-headline>
    </header>

    <k-input
      v-if="search"
      v-model="q"
      :autofocus="true"
      :placeholder="$t('search') + ' …'"
      type="text"
      class="k-dialog-search"
      icon="search"
    />

    <template v-if="options.length">
      <k-items
        :items="options"
        :link="false"
        layout="list"
        :sortable="false"
        @item="toggle"
      >
        <template #options="{ item: option }">
          <k-button
            v-if="selected.includes(option.id)"
            :autofocus="true"
            :icon="multiple ? 'check' : 'circle-filled'"
            :tooltip="$t('remove')"
            theme="positive"
            @click="toggle(option)"
          />
          <k-button
            v-else
            :autofocus="true"
            :tooltip="$t('select')"
            icon="circle-outline"
            @click="toggle(option)"
          />

          <k-button
            v-if="option"
            :disabled="!option.hasChildren"
            :tooltip="$t('open')"
            icon="angle-right"
            @click.stop="go(option)"
          />
        </template>
      </k-items>

      <k-pagination
        :details="true"
        :dropdown="false"
        v-bind="pagination"
        align="center"
        class="k-dialog-pagination"
        @paginate="paginate"
      />
    </template>
    <k-empty v-else icon="page">
      {{ $t("dialog.pages.empty") }}
    </k-empty>
  </k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";
import debounce from "@/helpers/debounce.js";

export default {
  mixins: [DialogMixin],
  props: {
    multiple: Boolean,
    options: Array,
    parent: Object,
    search: Boolean,
    size: {
      type: String,
      default: "medium"
    },
    submitButton: {
      type: [String, Boolean],
      default() {
        return window.panel.$t("save");
      }
    }
  },
  data() {
    return {
      selected: [],
      q: null
    };
  },
  computed: {},
  watch: {
    q() {
      this.updateSearch();
    }
  },
  created() {
    this.updateSearch = debounce(this.updateSearch, 200);
  },
  methods: {
    back() {},
    go(page) {},
    toggle(item) {},
    updateSearch() {}
  }
};
</script>

<style>
.k-picker-dialog-navbar {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.5rem;
  padding-inline-end: 38px;
}
.k-picker-dialog-navbar .k-button {
  width: 38px;
}
.k-picker-dialog-navbar .k-button[disabled] {
  opacity: 0;
}
.k-picker-dialog-navbar .k-headline {
  flex-grow: 1;
  text-align: center;
}
.k-picker-dialog .k-list-item {
  cursor: pointer;
}
.k-picker-dialog .k-list-item .k-button[data-theme="disabled"],
.k-picker-dialog .k-list-item .k-button[disabled] {
  opacity: 0.25;
}
.k-picker-dialog .k-list-item .k-button[data-theme="disabled"]:hover {
  opacity: 1;
}
</style>
