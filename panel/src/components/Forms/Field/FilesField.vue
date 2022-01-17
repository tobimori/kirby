<script>
import PickerField from "./PickerField.vue";

/**
 * @example <k-files-field v-model="files" name="files" label="Files" />
 */
export default {
  extends: PickerField,
  props: {
    icon: {
      type: String,
      default: "image"
    },
    uploads: [Boolean, Object, Array]
  },
  computed: {
    hasDropzone() {
      return this.canAddMore;
    },
    hasUploads() {
      return true;
    },
    moreUpload() {
      return this.more && this.uploads;
    },
    options() {
      if (this.uploads) {
        return {
          icon: this.canAddMore ? "add" : "refresh",
          text: this.canAddMore ? this.$t("add") : this.$t("change"),
          options: [
            { icon: "check", text: this.$t("select"), option: "picker" },
            { icon: "upload", text: this.$t("upload"), option: "upload" }
          ]
        };
      }

      return {
        options: [{ icon: "check", text: this.$t("select"), option: "picker" }]
      };
    },
    uploadParams() {
      return {
        accept: this.uploads.accept,
        max: this.max,
        multiple: this.multiple,
        url: this.$urls.api + "/" + this.endpoints.field + "/upload"
      };
    }
  },
  created() {
    this.$events.$on("file.delete", this.removeById);
  },
  destroyed() {
    this.$events.$off("file.delete", this.removeById);
  },
  methods: {
    onDrop(files) {
      if (this.uploads === false) {
        return false;
      }

      return this.$refs.upload.drop(files, this.uploadParams);
    },
    onOption(option) {
      switch (option) {
        case "picker":
          return this.open();
        case "upload":
          return this.$refs.upload.open(this.uploadParams);
      }
    }
  }
};
</script>
