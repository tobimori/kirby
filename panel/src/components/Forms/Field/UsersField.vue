<script>
import PickerField from "./PickerField.vue";

export default {
  extends: PickerField,
  props: {
    icon: {
      type: String,
      default: "users"
    }
  }
};
</script>
