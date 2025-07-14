import { defineStore } from 'pinia'
import axios from 'axios'

export const useOnboardingStore = defineStore('onboarding', {
  state: () => ({
    organizationName: '',
    educationSystems: [],
    country: '',
    region: '',
    contactEmail: '',
    contactPhone: '',
    website: '',
    terms: '',
    streams: '',
    levels: [],
    services: [],
    custom: '',
    logo: null,
    background: null,
    brandColor: '#2563eb',
    slogan: '',
    curriculum_key: '',
    curriculumKeys: [],

    // Structure from curriculum setup
    structure: {},
    curriculumData: null,

    // ✅ NEW: Facilities Structure
facilities: {
  // Core educational facilities — expected in most schools
  core: [],

  // Specialized facilities — present in some schools
  specialized: [],

  
  // ✅ New flattened transport block
  transport: {
    parkingSpaces: null,
    vehicles: [] // Each: { type: '', capacity: null }
  },

  // Optional: spatial layout for future 3D mapping
  layout: []
}

  }),

  actions: {
    async fetchSchoolInfo() {
      try {
        await axios.get('/sanctum/csrf-cookie');
        const { data } = await axios.get('/api/user/school');
        
        // Handle new response format
        if (data.success && data.school) {
          this.organizationName = data.school.name;
          this.curriculum_key = data.school.curriculum_key;
        } else {
          // Fallback for old format
          this.organizationName = data.name;
          this.curriculum_key = data.curriculum_key;
        }
      } catch (error) {
        console.error('Failed to fetch school info:', error);
        throw error;
      }
    },

showToast(message, type = 'info') {
  alert(`${type.toUpperCase()}: ${message}`)
},


    async updateSchoolCurriculum(curriculumKey) {
      try {
        await axios.get('/sanctum/csrf-cookie');
        const { data } = await axios.post('/api/school/update-curriculum', {
          curriculum_key: curriculumKey
        });
        this.curriculum_key = curriculumKey;
        return data;
      } catch (error) {
        console.error('Error updating curriculum Key:', error);
        throw error;
      }
    },

generateClassroomsFromStructure() {
  const classroomsByLevel = [];
  const streams = this.streams
    ? this.streams.split(',').map(s => s.trim()).filter(Boolean)
    : [];

  this.levels.forEach(level => {
    const levelName = level.name.trim();
    const classList = level.classes
      ? level.classes.split(',').map(cls => cls.trim()).filter(Boolean)
      : [];

    const classrooms = [];

    // ✅ Case: No class list or class list is the same as streams
    const isClassSameAsStreams = classList.length === streams.length &&
      classList.every(cls => streams.includes(cls));

    if (classList.length === 0 || isClassSameAsStreams) {
      // Just stream-based classrooms
      streams.forEach(stream => {
        classrooms.push({
          level: levelName,
          class: stream,
          stream: null,
          name: `${levelName} ${stream}`, // <- 👈 FORMAT FIXED
          code: `${levelName.replace(/\s+/g, '')}-${stream.replace(/\s+/g, '')}`,
          id: `${levelName}-${stream}`.replace(/\s+/g, '-').toLowerCase()
        });
      });
    } else {
      // Full class × stream
      classList.forEach(cls => {
        if (streams.length > 0) {
          streams.forEach(stream => {
            classrooms.push({
              level: levelName,
              class: cls,
              stream,
              name: `${levelName} ${cls} ${stream}`,
              code: `${levelName.replace(/\s+/g, '')}-${cls}${stream}`.replace(/\s+/g, ''),
              id: `${levelName}-${cls}-${stream}`.replace(/\s+/g, '-').toLowerCase()
            });
          });
        } else {
          classrooms.push({
            level: levelName,
            class: cls,
            stream: null,
            name: `${levelName} ${cls}`,
            code: `${levelName.replace(/\s+/g, '')}-${cls}`,
            id: `${levelName}-${cls}`.replace(/\s+/g, '-').toLowerCase()
          });
        }
      });
    }

    classroomsByLevel.push({
      level: levelName,
      classrooms
    });
  });

  return classroomsByLevel;
},



    updateStepData(data) {
      Object.assign(this, data);
    },

    setStructure(payload) {
    if (
        !payload ||
        typeof payload !== 'object' ||
        Array.isArray(payload) ||
        Object.keys(payload).length === 0
    ) {
        console.warn('⛔️ Ignoring invalid structure payload:', payload)
        return
    }

        this.structure = payload
    },

    setCurriculumData(payload) {
      this.curriculumData = payload;
    },

    // ✅ NEW: Facility setter (you can update pieces if needed)
    setFacilities(payload) {
      this.facilities = payload;
    },

    resetAll() {
      this.$reset();
    }
  },

  persist: true
});

