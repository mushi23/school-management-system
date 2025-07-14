<template>
  <div class="school-form-container">
    <h2 class="form-title">School Account Creation</h2>

    <form @submit.prevent="submitForm" class="form-body">

      <!-- County dropdown -->
      <div class="form-group">
        <label for="county">County</label>
        <select
          id="county"
          v-model="form.county"
          @change="onCountyChange"
          required
        >
          <option value="" disabled>Select County</option>
          <option
            v-for="county in counties"
            :key="county"
            :value="county"
          >
            {{ county }}
          </option>
        </select>
      </div>

      <!-- Sub-County dropdown -->
      <div class="form-group">
        <label for="sub_county">Sub County</label>
        <select
          id="sub_county"
          v-model="form.sub_county"
          @change="onSubCountyChange"
          :disabled="!form.county"
          required
        >
          <option value="" disabled>Select Sub County</option>
          <option
            v-for="sub in filteredSubCounties"
            :key="sub"
            :value="sub"
          >
            {{ sub }}
          </option>
        </select>
      </div>

      <!-- School dropdown -->
      <div class="form-group">
        <label for="school">School</label>
        <select
          id="school"
          v-model="form.schoolName"
          @change="onSchoolChange"
          :disabled="!form.sub_county"
          required
        >
          <option value="" disabled>Select School</option>
          <option
            v-for="school in schoolsInSubCounty"
            :key="school.code"
            :value="school.name"
          >
            {{ school.name }}
          </option>
        </select>
      </div>

      <!-- Total students (readonly) -->
      <div class="form-group">
        <label for="total_students">Total Students</label>
        <input
          id="total_students"
          type="number"
          :value="selectedSchool ? selectedSchool.total : ''"
          readonly
          placeholder="Total students"
        />
      </div>

      <!-- School Code (user inputs manually) -->
      <div class="form-group">
        <label for="school_code">School Code</label>
        <input
          id="school_code"
          v-model="form.schoolCode"
          type="text"
          placeholder="Enter school code"
          :class="{ 'input-valid': isSchoolCodeValid }"
          required
        />
      </div>

      <!-- Contact Email -->
      <div class="form-group">
        <label for="contact_email">Email</label>
        <input
          id="contact_email"
          v-model="form.contact_email"
          type="email"
          placeholder="Enter email"
          required
        />
      </div>

      <!-- Contact Phone -->
      <div class="form-group">
        <label for="contact_phone">Phone</label>
        <input
          id="contact_phone"
          v-model="form.contact_phone"
          type="text"
          placeholder="Enter phone number"
          required
        />
      </div>

      <button type="submit" :disabled="loading || !isSchoolCodeValid">
  <span v-if="loading">
    <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
    Creating...
  </span>
  <span v-else>
    Create Account
  </span>
</button>


      <p v-if="successMessage" class="success">{{ successMessage }}</p>
      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

      <p v-if="form.schoolCode && !isSchoolCodeValid" class="error">
        School code does not match the selected school.
      </p>
    </form>
  </div>
</template>

<script>
import schoolsData from '../data/schools.json'; // adjust path as needed
import axios from 'axios';
import { useToast } from 'vue-toastification';

export default {
  name: 'SchoolAccountForm',
  setup() {
    const toast = useToast();

    return {
      toast,
    };
  },
  data() {
    return {
      form: {
        county: '',
        sub_county: '',
        schoolName: '',
        schoolCode: '',
        contact_email: '',
        contact_phone: '',
      },
      loading: false,
      successMessage: '',
      errorMessage: '',
      schoolsData,
    };
  },
  computed: {
    counties() {
      return Object.keys(this.schoolsData);
    },
    filteredSubCounties() {
      if (!this.form.county) return [];
      return Object.keys(this.schoolsData[this.form.county] || {});
    },
    schoolsInSubCounty() {
      if (
        !this.form.county ||
        !this.form.sub_county ||
        !this.schoolsData[this.form.county]
      ) return [];
      return this.schoolsData[this.form.county][this.form.sub_county] || [];
    },
    selectedSchool() {
      return this.schoolsInSubCounty.find(
        (school) => school.name === this.form.schoolName
      );
    },
    isSchoolCodeValid() {
      if (!this.form.schoolCode || !this.selectedSchool) return false;
      return this.form.schoolCode.trim().toUpperCase() === this.selectedSchool.code.toUpperCase();
    },
  },
  methods: {
    onCountyChange() {
      this.form.sub_county = '';
      this.form.schoolName = '';
      this.form.schoolCode = '';
      this.successMessage = '';
      this.errorMessage = '';
    },
    onSubCountyChange() {
      this.form.schoolName = '';
      this.form.schoolCode = '';
      this.successMessage = '';
      this.errorMessage = '';
    },
    onSchoolChange() {
      this.form.schoolCode = '';
      this.successMessage = '';
      this.errorMessage = '';
    },
    
    async submitForm() {
      if (!this.isSchoolCodeValid) {
        this.errorMessage = 'School code does not match the selected school.';
        this.toast.error(this.errorMessage, { timeout: 2000 });
        return;
      }

      this.loading = true;
      this.successMessage = '';
      this.errorMessage = '';

      try {
        await axios.get('/sanctum/csrf-cookie');
        const authResponse = await axios.get('/api/me');
        const payload = {
          county: this.form.county,
          sub_county: this.form.sub_county,
          school_name: this.form.schoolName,
          school_code: this.form.schoolCode,
          total_students: this.selectedSchool.total,
          contact_email: this.form.contact_email,
          contact_phone: this.form.contact_phone,
        };

        await axios.post('/api/admin/schools', payload);

        this.successMessage = 'Account created successfully!';
        this.toast.success(this.successMessage, { timeout: 2000 });
        this.resetForm();
        this.$emit('close');
      } catch (error) {
        console.error('Submit Error:', error);
        
        if (error.response?.data?.errors) {
          const messages = Object.values(error.response.data.errors).flat();
          this.errorMessage = messages.join(' | ');
        } else if (error.response?.data?.message) {
          this.errorMessage = error.response.data.message;
        } else if (error.message) {
          this.errorMessage = error.message;
        } else {
          this.errorMessage = 'An unknown error occurred.';
        }
        this.toast.error(this.errorMessage, { timeout: 2000 });
      } finally {
        this.loading = false;
      }
    },
    resetForm() {
      this.form = {
        county: '',
        sub_county: '',
        schoolName: '',
        schoolCode: '',
        contact_email: '',
        contact_phone: '',
      };
    },
  },
};
</script>

<style scoped>
.school-form-container {
  max-height: 80vh;
  overflow-y: auto;
  padding: 1.5rem;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.input {
  border: 1px solid #ccc;
  border-radius: 4px;
  padding: 0.5rem;
  transition: border-color 0.2s ease, background-color 0.2s ease;
}

/* Focused inputs/selects use blue */
input:focus,
select:focus {
  border-color: #3B82F6; /* blue-500 */
  background-color: #fff;
  outline: none;
}

/* Valid input: turns light blue inside */
.input-valid {
  background-color: #DBEAFE; /* light blue-200 */
  border-color: #3B82F6;     /* blue-500 border */
}

.form-title {
  text-align: center;
  margin-bottom: 1.5rem;
  font-size: 1.5rem;
  font-weight: bold;
}

.form-body {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

label {
  margin-bottom: 0.25rem;
  font-weight: bold;
}

input,
select {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

input[readonly] {
  background-color: #eee;
}

/* Blue background buttons */
button {
  background-color: #3B82F6; /* blue-500 */
  color: white;
  padding: 0.75rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #2563EB; /* blue-600 */
}

button[disabled] {
  opacity: 0.7;
  cursor: not-allowed;
}

.success {
  color: green;
  margin-top: 0.5rem;
}

.error {
  color: red;
  margin-top: 0.5rem;
}
/* Dark mode overrides */
.dark .school-form-container {
  background-color: #1f2937; /* Tailwind gray-800 */
  color: #f9fafb;             /* Tailwind gray-100 */
}

.dark input,
.dark select {
  background-color: #374151; /* gray-700 */
  border-color: #4b5563;     /* gray-600 */
  color: #f9fafb;            /* gray-100 */
}

.dark input[readonly] {
  background-color: #4b5563;
}

.dark button {
  background-color: #3B82F6; /* blue-500 (unchanged) */
  color: white;
}

.dark button:hover {
  background-color: #2563EB; /* blue-600 */
}

.dark .success {
  color: #86efac; /* green-300 */
}

.dark .error {
  color: #fca5a5; /* red-300 */
}

</style>

