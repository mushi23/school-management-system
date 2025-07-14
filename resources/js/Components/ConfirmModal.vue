<script setup>
import { Dialog, Transition } from '@headlessui/vue'
import { ref, Fragment } from 'vue'

defineProps({
  modelValue: Boolean,
  message: {
    type: String,
    default: 'Are you sure?',
  },
})

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

const close = () => emit('update:modelValue', false)
const confirm = () => {
  emit('confirm')
  close()
}
const cancel = () => {
  emit('cancel')
  close()
}
</script>

<template>
  <Transition appear show="modelValue" as="template">
    <Dialog
      as="div"
      class="fixed inset-0 z-10 overflow-y-auto"
      @close="cancel"
    >
      <div class="min-h-screen px-4 text-center">
        <Transition.Child
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <Dialog.Overlay class="fixed inset-0 bg-black opacity-30" />
        </Transition.Child>

        <!-- Trick the browser into centering the modal contents -->
        <span
          class="inline-block h-screen align-middle"
          aria-hidden="true"
          >&#8203;</span
        >

        <Transition.Child
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <div
            class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle
              bg-white shadow-xl rounded-lg"
          >
            <Dialog.Title
              as="h3"
              class="text-lg font-medium leading-6 text-gray-900"
            >
              Confirmation
            </Dialog.Title>
            <div class="mt-2">
              <p class="text-sm text-gray-500">{{ message }}</p>
            </div>

            <div class="mt-4 flex justify-end space-x-3">
              <button
                type="button"
                class="inline-flex justify-center px-4 py-2 text-sm font-medium
                  text-gray-700 bg-gray-100 border border-transparent rounded-md
                  hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2
                  focus:ring-indigo-500"
                @click="cancel"
              >
                Cancel
              </button>
              <button
                type="button"
                class="inline-flex justify-center px-4 py-2 text-sm font-medium
                  text-white bg-indigo-600 border border-transparent rounded-md
                  hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2
                  focus:ring-indigo-500"
                @click="confirm"
              >
                Confirm
              </button>
            </div>
          </div>
        </Transition.Child>
      </div>
    </Dialog>
  </Transition>
</template>


