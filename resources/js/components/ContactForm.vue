<template>
  <div class="card">
    <div class="card-header">
      <h2>Contact Form</h2>
    </div>
    <div class="card-body">
      <div class="col-md-6 mx-auto">
        <form @submit.prevent="onSubmit" id="contact-form">
          <div class="form-group">
            <label for="name" class="form-label">Name:</label>
            <input type="text" id="name" v-model="name" class="form-control" minlength="2" maxlength="10" />
            <div class="error text-danger" v-for="(error, index) in v$.name.$errors" :key="index">
              {{ error.$message }}
            </div>
          </div>

          <div class="form-group my-2">
            <label for="email" class="form-label">Email:</label>
            <input type="email" id="email" v-model="email" class="form-control" />
            <div class="error text-danger" v-for="(error, index) in v$.email.$errors" :key="index">
              {{ error.$message }}
            </div>
          </div>

          <div class="form-group">
            <label for="phone" class="form-label">Phone:</label>
            <input type="tel" id="phone" v-model="phone" class="form-control" minlength="10" maxlength="13" />
            <div class="error text-danger" v-for="(error, index) in v$.phone.$errors" :key="index">
              {{ error.$message }}
            </div>
          </div>

          <div class="form-group my-2">
            <label for="message" class="form-label">Message:</label>
            <textarea id="message" v-model="message" class="form-control" minlength="10"></textarea>
            <div class="error text-danger" v-for="(error, index) in v$.message.$errors" :key="index">
              {{ error.$message }}
            </div>
          </div>

          <div class="form-group">
            <label for="street" class="form-label">Street:</label>
            <input type="text" id="street" v-model="street" class="form-control" />
            <div class="error text-danger" v-for="(error, index) in v$.street.$errors" :key="index">
              {{ error.$message }}
            </div>
          </div>

          <div class="form-group my-2">
            <label for="state" class="form-label">State:</label>
            <input type="text" id="state" v-model="state" class="form-control" />
            <div class="error text-danger" v-for="(error, index) in v$.state.$errors" :key="index">
              {{ error.$message }}
            </div>
          </div>

          <div class="form-group">
            <label for="zip" class="form-label">Zip:</label>
            <input type="text" id="zip" v-model="zip" class="form-control" minlength="6" maxlength="6" />
            <div class="error text-danger" v-for="(error, index) in v$.zip.$errors" :key="index">
              {{ error.$message }}
            </div>
          </div>

          <div class="form-group my-2">
            <label for="country" class="form-label">Country:</label>
            <input type="text" id="country" v-model="country" class="form-control" />
            <div class="error text-danger" v-for="(error, index) in v$.country.$errors" :key="index">
              {{ error.$message }}
            </div>
          </div>

          <div class="form-group">
            <label for="images" class="form-label">Images:</label>
            <input type="file" id="images" accept="image/jpg, image/jpeg" @change="onImagesChange" multiple
              class="form-control" />
            <div v-if="errors.images" class="error text-danger">{{ errors.images }}</div>
          </div>

          <div class="form-group my-2">
            <label for="files" class="form-label">Files:</label>
            <input type="file" id="files" accept="application/pdf" @change="onFilesChange" multiple
              class="form-control" />
            <div v-if="errors.files" class="error text-danger">{{ errors.files }}</div>
          </div>

          <div class="d-flex align-items-center justify-content-end py-2">
            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { reactive, toRefs } from "vue";
import { useVuelidate } from '@vuelidate/core'
import { required, minLength, maxLength, email, helpers } from "@vuelidate/validators";
import axios from "axios";

const validDomain = (value) => {
  const domain = value.split("@")[1];
  return domain !== "gmail.com";
};

const mustBeWithPrefix = (value) => {
  const phoneNumberPattern = /^\+\d{1,3}\d{10}$/;
  return phoneNumberPattern.test(value);
}

export default {
  setup() {
    let isSubmitting = reactive(false);
    const state = reactive({
      name: "",
      email: "",
      phone: "",
      message: "",
      street: "",
      state: "",
      zip: "",
      country: "",
      images: [],
      files: [],
      errors: {},
    });

    const rules = {
      name: {
        required: helpers.withMessage('Please enter your name.', required),
        minLength: minLength(2),
        maxLength: maxLength(10),
      },
      email: {
        required: helpers.withMessage('Please enter your email.', required),
        email: helpers.withMessage('Please enter a valid email.', email),
        validDomain: helpers.withMessage('Please enter a valid email. (Not from gmail.com)', validDomain),
      },
      phone: {
        required: helpers.withMessage('Please enter your phone number.', required),
        mustBeWithPrefix: helpers.withMessage('Please enter a valid phone number with country code.', mustBeWithPrefix),
      },
      message: {
        required: helpers.withMessage('Please enter your message.', required),
        minLength: minLength(10),
      },
      street: {
        required: helpers.withMessage('Please enter your street.', required),
      },
      state: {
        required: helpers.withMessage('Please enter your state.', required),
      },
      zip: {
        required: helpers.withMessage('Please enter your zip.', required),
      },
      country: {
        required: helpers.withMessage('Please enter your country.', required),
      },
    };

    const v$ = useVuelidate(rules, state);

    const onSubmit = async () => {
      const isFormValid = await v$.value.$validate();
      if (!isFormValid) {
        return;
      }

      isSubmitting = true;

      const formData = new FormData();
      formData.append("name", state.name);
      formData.append("email", state.email);
      formData.append("phone", state.phone);
      formData.append("message", state.message);
      formData.append("street", state.street);
      formData.append("state", state.state);
      formData.append("zip", state.zip);
      formData.append("country", state.country);

      for (const image of state.images) {
        formData.append("images", image);
      }

      for (const file of state.files) {
        formData.append("files", file);
      }

      axios
        .post("/submissions", formData)
        .then((response) => {
          if (response.data.status === "success") {
            alert(response.data.message);
            document.querySelector("#contact-form") && document.querySelector("#contact-form").reset();
          }
        })
        .catch((error) => {
          alert(error.message ?? "Something went wrong! Please try again later.");
        }).finally(() => {
          isSubmitting = false;
        });
    };

    const onImagesChange = (event) => {
      const allowedTypes = ["image/jpg", "image/jpeg"];
      const maxSize = 1 * 1024 * 1024;

      for (const file of event.target.files) {
        if (!allowedTypes.includes(file.type)) {
          state.errors['images'] = "Only jpg files are allowed";
          state.images = [];
          document.querySelector("#images").value = ""

          return;
        }

        if (file.size > maxSize) {
          state.errors['images'] = "Image size should be less than 1MB";
          state.images = [];
          document.querySelector("#images").value = ""

          return;
        }

        state.errors['images'] = "";
      }

      state.images = event.target.files;
    };

    const onFilesChange = (event) => {
      const allowedTypes = ["application/pdf"];
      const maxSize = 1 * 1024 * 1024;

      for (const file of event.target.files) {
        if (!allowedTypes.includes(file.type)) {
          state.errors['files'] = "Only pdf files are allowed";
          state.files = [];
          document.querySelector("#files").value = ""

          return;
        }

        if (file.size > maxSize) {
          state.errors['files'] = "File size should be less than 1MB";
          state.files = [];
          document.querySelector("#files").value = ""

          return;
        }

        state.errors['files'] = "";
      }

      state.files = event.target.files;
    };

    return {
      ...toRefs(state),
      onSubmit,
      onImagesChange,
      onFilesChange,
      isSubmitting,
      v$,
    };
  },
};
</script>
