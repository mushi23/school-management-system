<template>
  <div class="form-container">
    <h2 class="form-title">Register New Student</h2>

    <form @submit.prevent="submitForm" class="form-body">

      <!-- Full Name -->
      <div class="form-group">
        <label for="full_name">Full Name</label>
        <input
          id="full_name"
          v-model="form.full_name"
          type="text"
          required
        />
      </div>

      <!-- Date of Birth -->
      <div class="form-group">
        <label for="dob">Date of Birth</label>
        <input
          id="dob"
          v-model="form.dob"
          type="date"
          required
        />
        <!-- Replace with modal calendar later -->
      </div>

      <!-- Profile Picture -->
      <div class="form-group">
        <label for="profile_picture">Profile Picture</label>
        <input
          id="profile_picture"
          type="file"
          @change="handleImageUpload"
          accept="image/*"
        />
      </div>

      <!-- Email -->
      <div class="form-group">
        <label for="email">Email</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
        />
      </div>

      <!-- Guardian Phone Number -->
      <div class="form-group">
        <label for="guardian_phone">Parent/Guardian Phone</label>
        <input
          id="guardian_phone"
          v-model="form.guardian_phone"
          type="tel"
          placeholder="e.g. +2547XXXXXXXX"
          required
        />
      </div>

      <!-- Class Assignment -->
      <div class="form-group">
        <label for="class_id">Class</label>
        <select v-model="form.class_id" id="class_id" required>
          <option value="" disabled>Select class</option>
          <option v-for="cls in dummyClasses" :key="cls.id" :value="cls.id">
            {{ cls.name }}
          </option>
        </select>
      </div>

      <!-- Submit Button -->
      <button type="submit" :disabled="loading">
        <span v-if="loading">Submitting...</span>
        <span v-else>Create Student</span>
      </button>

      <p v-if="successMessage" class="success">{{ successMessage }}</p>
      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

    </form>
  </div>
</template>

<script>
export default {
  name: 'StudentCreateForm',
  data() {
    return {
      form: {
        full_name: '',
        dob: '',
        email: '',
        guardian_phone: '',
        class_id: '',
        profile_picture: null,
      },
      dummyClasses: [
        { id: 1, name: 'Grade 1' },
        { id: 2, name: 'Grade 2' },
        { id: 3, name: 'Grade 3' },
      ],
      loading: false,
      successMessage: '',
      errorMessage: '',
    };
  },
  methods: {
    handleImageUpload(event) {
      this.form.profile_picture = event.target.files[0];
    },
    submitForm() {
      // Dummy submission logic for now
      this.successMessage = '';
      this.errorMessage = '';
      this.loading = true;

      setTimeout(() => {
        this.successMessage = 'Student form filled successfully (mock only)';
        this.loading = false;
      }, 1000);
    },
  },
};
</script>

<style scoped>
/* Use your previous styles or customize */

.form-container {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  max-width: 600px;
  margin: auto;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-title {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  font-weight: bold;
}

.form-body {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

input, select {
  padding: 0.5rem;
  border-radius: 4px;
  border: 1px solid #ccc;
}

button {
  background-color: #2563EB;
  color: white;
  padding: 0.75rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.success {
  color: green;
}
.error {
  color: red;
}
</style>


