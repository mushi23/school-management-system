<template>
  <div class="w-full max-w-6xl mx-auto mt-12">
    <div class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-lg dark:shadow-blue-950/40 p-8">
      <div class="flex items-center justify-between mb-8">
        <div>
          <h2 class="text-2xl font-bold text-blue-700 dark:text-blue-200 mb-2">Receipt Template Settings</h2>
          <p class="text-gray-600 dark:text-gray-300">Customize your school's receipt appearance and branding</p>
        </div>
        <div class="flex items-center gap-4">
          <button
            @click="saveTemplate"
            :disabled="isSaving"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200 disabled:opacity-50"
          >
            <i v-if="isSaving" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-save mr-2"></i>
            {{ isSaving ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-12">
        <div class="text-center">
          <i class="fas fa-spinner fa-spin text-4xl text-blue-500 mb-4"></i>
          <p class="text-gray-600 dark:text-gray-300">Loading template settings...</p>
        </div>
      </div>

      <!-- Template Settings Form -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Column - Basic Settings -->
        <div class="space-y-6">
          <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Basic Information</h3>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Template Name
                </label>
                <input
                  v-model="template.name"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Default Receipt Template"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  School Slogan
                </label>
                <input
                  v-model="template.slogan"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Excellence in Education"
                />
              </div>

              <!-- Remove logo upload UI and logic
              // Instead, always display the school logo from the backend
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  School Logo
                </label>
                <div class="flex items-center gap-4">
                  <div v-if="template.logo_url" class="flex-shrink-0">
                    <img :src="template.logo_url" alt="School Logo" class="w-16 h-16 object-contain border border-gray-300 dark:border-gray-600 rounded-lg" />
                  </div>
                  <div class="flex-1">
                    <input
                      ref="logoInput"
                      type="file"
                      accept="image/*"
                      @change="handleLogoUpload"
                      class="hidden"
                    />
                    <button
                      @click="$refs.logoInput.click()"
                      class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
                    >
                      <i class="fas fa-upload mr-2"></i>
                      {{ template.logo_url ? 'Change Logo' : 'Upload Logo' }}
                    </button>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                      Recommended: 300x100px, PNG or JPG
                    </p>
                  </div>
                </div>
              </div>
              -->
              <div class="mb-4 border-b pb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">School Logo</label>
                <div class="flex items-center gap-4">
                  <img v-if="schoolLogoUrl" :src="schoolLogoUrl" alt="School Logo" class="w-24 h-16 object-contain border rounded bg-white dark:bg-gray-900" />
                  <span v-else class="text-xs text-gray-500">No logo available</span>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                  The school logo is managed by the school profile. To change it, update the school profile settings.
                </div>
              </div>
            </div>
          </div>

          <!-- Colors -->
          <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Brand Color</h3>
            <div class="flex items-center gap-4 mb-2">
              <div class="w-10 h-10 rounded-full border-2 border-gray-300 dark:border-gray-600" :style="{ backgroundColor: schoolBrandColor || '#2563eb' }"></div>
              <span class="font-mono text-sm">{{ schoolBrandColor || '#2563eb' }}</span>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
              The brand color is managed by the school profile. To change it, update the school profile settings.
            </div>
          </div>

          <!-- Fonts -->
          <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Fonts</h3>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Primary Font
                </label>
                <select
                  v-model="template.primary_font"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="Arial, sans-serif">Arial</option>
                  <option value="Helvetica, sans-serif">Helvetica</option>
                  <option value="Times New Roman, serif">Times New Roman</option>
                  <option value="Georgia, serif">Georgia</option>
                  <option value="Verdana, sans-serif">Verdana</option>
                  <option value="Courier New, monospace">Courier New</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Secondary Font
                </label>
                <select
                  v-model="template.secondary_font"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="Georgia, serif">Georgia</option>
                  <option value="Times New Roman, serif">Times New Roman</option>
                  <option value="Arial, sans-serif">Arial</option>
                  <option value="Helvetica, sans-serif">Helvetica</option>
                  <option value="Verdana, sans-serif">Verdana</option>
                  <option value="Courier New, monospace">Courier New</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Display Options -->
        <div class="space-y-6">
          <!-- Display Options -->
          <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Display Options</h3>
            
            <div class="space-y-3">
              <label class="flex items-center">
                <input
                  v-model="template.show_school_logo"
                  type="checkbox"
                  class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                />
                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Show school logo</span>
              </label>

              <label class="flex items-center">
                <input
                  v-model="template.show_school_slogan"
                  type="checkbox"
                  class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                />
                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Show school slogan</span>
              </label>

              <label class="flex items-center">
                <input
                  v-model="template.show_school_address"
                  type="checkbox"
                  class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                />
                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Show school address</span>
              </label>

              <label class="flex items-center">
                <input
                  v-model="template.show_school_phone"
                  type="checkbox"
                  class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                />
                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Show school phone</span>
              </label>

              <label class="flex items-center">
                <input
                  v-model="template.show_school_email"
                  type="checkbox"
                  class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                />
                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Show school email</span>
              </label>

              <hr class="border-gray-300 dark:border-gray-600" />

              <label class="flex items-center">
                <input
                  v-model="template.show_receipt_number"
                  type="checkbox"
                  class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                />
                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Show receipt number</span>
              </label>

              <label class="flex items-center">
                <input
                  v-model="template.show_payment_date"
                  type="checkbox"
                  class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                />
                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Show payment date</span>
              </label>

              <label class="flex items-center">
                <input
                  v-model="template.show_payment_method"
                  type="checkbox"
                  class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                />
                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Show payment method</span>
              </label>

              <label class="flex items-center">
                <input
                  v-model="template.show_student_details"
                  type="checkbox"
                  class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                />
                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Show student details</span>
              </label>

              <label class="flex items-center">
                <input
                  v-model="template.show_fee_breakdown"
                  type="checkbox"
                  class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                />
                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Show fee breakdown</span>
              </label>

              <label class="flex items-center">
                <input
                  v-model="template.show_balance"
                  type="checkbox"
                  class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                />
                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Show balance information</span>
              </label>
            </div>
          </div>

          <!-- Logo Dimensions -->
          <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Logo Dimensions</h3>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Width (mm)
                </label>
                <input
                  v-model.number="template.logo_width"
                  type="number"
                  min="50"
                  max="300"
                  step="1"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Height (mm)
                </label>
                <input
                  v-model.number="template.logo_height"
                  type="number"
                  min="20"
                  max="200"
                  step="1"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Custom CSS -->
          <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Custom CSS</h3>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Additional CSS Styles
              </label>
              <textarea
                v-model="template.custom_css"
                rows="6"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                placeholder="/* Add custom CSS styles here */"
              ></textarea>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Add custom CSS to further customize the receipt appearance
              </p>
            </div>
          </div>

          <!-- Security Features -->
          <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Security Features</h3>
            
            <div class="space-y-6">
              <!-- Digital Signature -->
              <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="font-medium text-gray-900 dark:text-gray-100">Digital Signature</h4>
                  <label class="flex items-center">
                    <input
                      v-model="template.show_signature"
                      type="checkbox"
                      class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable</span>
                  </label>
                </div>
                
                <div v-if="template.show_signature" class="space-y-3">
                  <!-- Signature Options -->
                  <div class="flex items-center gap-2 mb-3">
                    <button
                      @click="showSignaturePad = true"
                      class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors duration-200"
                    >
                      <i class="fas fa-pen mr-1"></i>
                      Draw Signature
                    </button>
                    <span class="text-xs text-gray-500">or</span>
                    <button
                      @click="$refs.signatureInput.click()"
                      class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
                    >
                      <i class="fas fa-upload mr-1"></i>
                      Upload Image
                    </button>
                  </div>
                  
                  <!-- Hidden file input -->
                  <input
                    ref="signatureInput"
                    type="file"
                    accept="image/*"
                    @change="handleSignatureUpload"
                    class="hidden"
                  />
                  
                  <!-- Current Signature Display -->
                  <div v-if="template.signature_url" class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                      <img :src="template.signature_url" alt="Signature" class="w-24 h-12 object-contain border border-gray-300 dark:border-gray-600 rounded" />
                    </div>
                    <div class="flex-1">
                      <button
                        @click="removeSignature"
                        class="px-2 py-1 text-xs text-red-600 border border-red-300 rounded hover:bg-red-50 transition-colors duration-200"
                      >
                        <i class="fas fa-trash mr-1"></i>
                        Remove
                      </button>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Signatory Name
                      </label>
                      <input
                        v-model="template.signature_name"
                        type="text"
                        class="w-full px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded"
                        placeholder="e.g., John Doe"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Title
                      </label>
                      <input
                        v-model="template.signature_title"
                        type="text"
                        class="w-full px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded"
                        placeholder="e.g., Principal"
                      />
                    </div>
                  </div>
                  
                  <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Position
                    </label>
                    <select
                      v-model="template.signature_position"
                      class="w-full px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded"
                    >
                      <option value="bottom-right">Bottom Right</option>
                      <option value="bottom-left">Bottom Left</option>
                      <option value="bottom-center">Bottom Center</option>
                    </select>
                  </div>
                </div>
              </div>
              
              <!-- Signature Pad Modal -->
              <div v-if="showSignaturePad" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4">
                  <SignaturePad
                    :width="500"
                    :height="300"
                    @save="handleSignatureSave"
                    @cancel="showSignaturePad = false"
                  />
                </div>
              </div>

              <!-- School Seal -->
              <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="font-medium text-gray-900 dark:text-gray-100">School Seal/Stamp</h4>
                  <label class="flex items-center">
                    <input
                      v-model="template.show_seal"
                      type="checkbox"
                      class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable</span>
                  </label>
                </div>
                
                <div v-if="template.show_seal" class="space-y-3">
                  <SealUploader
                    :seal-url="template.seal_url"
                    :file-name="sealFileName"
                    :current-seal-style="template.seal_style"
                    @file-selected="handleSealUpload"
                    @remove="removeSeal"
                  />
                  <div class="text-xs text-gray-500 dark:text-gray-400 italic">
                    <i class="fas fa-info-circle"></i>
                    The seal position is automatically randomized for each receipt to avoid covering important text. Manual positioning is disabled.
                  </div>
                </div>
              </div>

              <!-- Watermark -->
              <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="font-medium text-gray-900 dark:text-gray-100">Watermark</h4>
                  <label class="flex items-center">
                    <input
                      v-model="template.show_watermark"
                      type="checkbox"
                      class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable</span>
                  </label>
                </div>
                
                <div v-if="template.show_watermark" class="space-y-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Watermark Text
                    </label>
                    <input
                      v-model="template.watermark_text"
                      type="text"
                      class="w-full px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded"
                      placeholder="e.g., ORIGINAL, PAID, VERIFIED"
                    />
                  </div>
                  
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Color
                      </label>
                      <input
                        v-model="template.watermark_color"
                        type="color"
                        class="w-full h-8 border border-gray-300 dark:border-gray-600 rounded"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Opacity
                      </label>
                      <input
                        v-model.number="template.watermark_opacity"
                        type="range"
                        min="0.05"
                        max="0.5"
                        step="0.05"
                        class="w-full"
                      />
                      <span class="text-xs text-gray-500">{{ (template.watermark_opacity * 100).toFixed(0) }}%</span>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Position
                      </label>
                      <select
                        v-model="template.watermark_position"
                        class="w-full px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded"
                      >
                        <option value="center">Center</option>
                        <option value="top-left">Top Left</option>
                        <option value="top-right">Top Right</option>
                        <option value="bottom-left">Bottom Left</option>
                        <option value="bottom-right">Bottom Right</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Rotation (degrees)
                      </label>
                      <input
                        v-model.number="template.watermark_rotation"
                        type="number"
                        min="-90"
                        max="90"
                        step="15"
                        class="w-full px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- QR Code -->
              <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="font-medium text-gray-900 dark:text-gray-100">QR Code</h4>
                  <label class="flex items-center">
                    <input
                      v-model="template.show_qr_code"
                      type="checkbox"
                      class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable</span>
                  </label>
                </div>
                
                <div v-if="template.show_qr_code" class="space-y-3">
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Position
                      </label>
                      <select
                        v-model="template.qr_code_position"
                        class="w-full px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded"
                      >
                        <option value="top-right">Top Right</option>
                        <option value="top-left">Top Left</option>
                        <option value="bottom-right">Bottom Right</option>
                        <option value="bottom-left">Bottom Left</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Size (mm)
                      </label>
                      <input
                        v-model.number="template.qr_code_size"
                        type="number"
                        min="20"
                        max="50"
                        step="1"
                        class="w-full px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded"
                      />
                    </div>
                  </div>
                  
                  <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Custom QR Data (optional)
                    </label>
                    <textarea
                      v-model="template.qr_code_data"
                      rows="2"
                      class="w-full px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded"
                      placeholder="Leave empty to use receipt details automatically"
                    ></textarea>
                  </div>
                </div>
              </div>

              <!-- Security Text -->
              <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="font-medium text-gray-900 dark:text-gray-100">Security Text</h4>
                  <label class="flex items-center">
                    <input
                      v-model="template.show_security_features"
                      type="checkbox"
                      class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable</span>
                  </label>
                </div>
                
                <div v-if="template.show_security_features" class="space-y-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Security Message
                    </label>
                    <input
                      v-model="template.security_text"
                      type="text"
                      class="w-full px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded"
                      placeholder="e.g., This receipt is computer generated and authentic"
                    />
                  </div>
                  
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Show Verification URL
                      </label>
                      <label class="flex items-center">
                        <input
                          v-model="template.show_verification_url"
                          type="checkbox"
                          class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                        />
                        <span class="ml-2 text-xs text-gray-700 dark:text-gray-300">Enable</span>
                      </label>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Show Unique ID
                      </label>
                      <label class="flex items-center">
                        <input
                          v-model="template.show_unique_id"
                          type="checkbox"
                          class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                        />
                        <span class="ml-2 text-xs text-gray-700 dark:text-gray-300">Enable</span>
                      </label>
                    </div>
                  </div>
                  
                  <div v-if="template.show_verification_url">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Verification URL
                    </label>
                    <input
                      v-model="template.verification_url"
                      type="url"
                      class="w-full px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded"
                      placeholder="https://yourschool.com/verify-receipt"
                    />
                  </div>
                  
                  <div v-if="template.show_unique_id">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Unique ID Prefix
                    </label>
                    <input
                      v-model="template.unique_id_prefix"
                      type="text"
                      class="w-full px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded"
                      placeholder="e.g., RCPT"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Preview Section -->
      <div class="mt-8 bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Preview</h3>
        
        <div class="bg-white dark:bg-gray-950 rounded-lg p-6 border border-gray-200 dark:border-gray-800 max-w-2xl relative" style="min-height: 600px;">
          <!-- Watermark -->
          <div v-if="template.show_watermark && template.watermark_text" 
               class="absolute pointer-events-none z-0"
               :style="{
                 top: template.watermark_position === 'center' ? '50%' : 
                      template.watermark_position.includes('top') ? '10%' : 'auto',
                 left: template.watermark_position === 'center' ? '50%' : 
                       template.watermark_position.includes('left') ? '10%' : 'auto',
                 right: template.watermark_position.includes('right') ? '10%' : 'auto',
                 bottom: template.watermark_position.includes('bottom') ? '10%' : 'auto',
                 transform: template.watermark_position === 'center' ? `translate(-50%, -50%) rotate(${template.watermark_rotation}deg)` : 'none',
                 color: template.watermark_color,
                 opacity: template.watermark_opacity,
                 fontSize: '48px',
                 fontWeight: 'bold',
                 textAlign: 'center',
                 width: '100%'
               }">
            {{ template.watermark_text }}
          </div>

          <!-- QR Code -->
          <div v-if="template.show_qr_code" 
               class="absolute z-10"
               :style="{
                 top: template.qr_code_position.includes('top') ? '20px' : 'auto',
                 right: template.qr_code_position.includes('right') ? '20px' : 'auto',
                 left: template.qr_code_position.includes('left') ? '20px' : 'auto',
                 bottom: template.qr_code_position.includes('bottom') ? '20px' : 'auto',
                 width: template.qr_code_size + 'mm',
                 height: template.qr_code_size + 'mm'
               }">
            <div class="w-full h-full bg-gray-200 flex items-center justify-center text-xs text-gray-500 border border-dashed border-gray-400 rounded">
              QR
            </div>
          </div>

          <!-- School Seal -->
          <div v-if="template.show_seal && template.seal_url" 
               class="absolute z-20"
               :style="{
                 top: template.seal_position.includes('top') ? '20px' : 'auto',
                 right: template.seal_position.includes('right') ? '20px' : 'auto',
                 left: template.seal_position.includes('left') ? '20px' : 'auto',
                 bottom: template.seal_position.includes('bottom') ? '20px' : 'auto',
                 width: template.seal_size + 'mm',
                 height: template.seal_size + 'mm'
               }">
            <img :src="template.seal_url" alt="School Seal" class="w-full h-full object-contain" />
          </div>

          <div class="text-center border-b-2 pb-4 mb-6" :style="{ borderColor: schoolBrandColor }">
            <div v-if="template.show_school_logo && schoolLogoUrl" class="mb-4">
              <img 
                :src="schoolLogoUrl" 
                alt="School Logo" 
                class="mx-auto"
                :style="{ 
                  maxWidth: template.logo_width + 'mm',
                  maxHeight: template.logo_height + 'mm'
                }"
              />
            </div>
            
            <div class="text-2xl font-bold mb-2 text-black dark:text-gray-100" :style="{ fontFamily: template.secondary_font }">
              School Name
            </div>
            
            <div v-if="template.show_school_slogan && template.slogan" class="italic mb-4 text-gray-700 dark:text-gray-300">
              {{ template.slogan }}
            </div>
            
            <div v-if="template.show_school_address || template.show_school_phone || template.show_school_email" class="text-sm text-gray-600 dark:text-gray-400">
              <div v-if="template.show_school_address">School Address</div>
              <div v-if="template.show_school_phone">Phone: +254 XXX XXX XXX</div>
              <div v-if="template.show_school_email">Email: info@school.com</div>
            </div>
          </div>

          <div class="mb-2 text-sm text-gray-700 dark:text-gray-300">
            <span v-if="schoolCounty">{{ schoolCounty }}</span>
            <span v-if="schoolSubCounty">, {{ schoolSubCounty }}</span>
            <span v-if="schoolAdminEmail || schoolAdminPhone" class="block mt-1">
              <span v-if="schoolAdminEmail">Email: {{ schoolAdminEmail }}</span>
              <span v-if="schoolAdminPhone" class="ml-2">Phone: {{ schoolAdminPhone }}</span>
            </span>
          </div>

          <div class="text-center mb-6">
            <div class="text-xl font-bold text-black dark:text-gray-100">OFFICIAL RECEIPT</div>
          </div>

          <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
              <div v-if="template.show_receipt_number" class="mb-3">
                <div class="font-bold text-sm text-black dark:text-gray-100">Receipt Number:</div>
                <div class="text-sm">SCH202412001</div>
              </div>
              <div v-if="template.show_payment_date" class="mb-3">
                <div class="font-bold text-sm text-black dark:text-gray-100">Payment Date:</div>
                <div class="text-sm">December 12, 2024</div>
              </div>
              <div v-if="template.show_payment_method" class="mb-3">
                <div class="font-bold text-sm text-black dark:text-gray-100">Payment Method:</div>
                <div class="text-sm">M-PESA</div>
              </div>
            </div>
            
            <div>
              <div v-if="template.show_student_details" class="mb-3">
                <div class="font-bold text-sm text-black dark:text-gray-100">Student Name:</div>
                <div class="text-sm">John Doe</div>
              </div>
              <div class="mb-3">
                <div class="font-bold text-sm text-black dark:text-gray-100">Term:</div>
                <div class="text-sm">Term 1</div>
              </div>
            </div>
          </div>

          <div v-if="template.show_fee_breakdown" class="mb-6">
            <div class="border rounded-lg overflow-hidden">
              <div class="p-3 font-bold text-white dark:text-gray-100" :style="{ backgroundColor: schoolBrandColor }">
                <div class="grid grid-cols-2">
                  <span>Description</span>
                  <span class="text-right">Amount (KES)</span>
                </div>
              </div>
              <div class="p-3 border-t">
                <div class="grid grid-cols-2">
                  <span>School Fees - Term 1</span>
                  <span class="text-right">5,000.00</span>
                </div>
              </div>
            </div>
          </div>

          <div class="text-right border-t-2 pt-4" :style="{ borderColor: schoolBrandColor }">
            <div class="mb-2">
              <span class="font-bold text-black dark:text-gray-100">Amount Paid:</span>
              <span class="font-bold ml-4 text-black dark:text-gray-100">KES 5,000.00</span>
            </div>
            <div v-if="template.show_balance" class="text-sm text-gray-700 dark:text-gray-300">
              <div>Balance Before: KES 10,000.00</div>
              <div>Balance After: KES 5,000.00</div>
            </div>
          </div>

          <!-- Digital Signature -->
          <div v-if="template.show_signature && template.signature_url" 
               class="mt-8"
               :style="{ textAlign: template.signature_position === 'bottom-left' ? 'left' : template.signature_position === 'bottom-center' ? 'center' : 'right' }">
            <img :src="template.signature_url" alt="Signature" class="max-w-[120px] max-h-[60px] mb-2" />
            <div class="font-bold text-sm text-black dark:text-gray-100">{{ template.signature_name }}</div>
            <div class="text-xs text-gray-600 dark:text-gray-400">{{ template.signature_title }}</div>
        </div>

          <!-- Security Text -->
          <div v-if="template.show_security_features && template.security_text" 
               class="mt-4 text-center text-xs text-gray-500 dark:text-gray-400">
            {{ template.security_text }}
          </div>
          <div v-if="template.show_verification_url && template.verification_url" 
               class="mt-2 text-center text-xs text-gray-500 dark:text-gray-400">
            Verify at: {{ template.verification_url }}
          </div>
          <div v-if="template.show_unique_id && template.unique_id_prefix" 
               class="mt-2 text-center text-xs text-gray-500 dark:text-gray-400">
            Unique ID: {{ template.unique_id_prefix }}12345
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import SchoolAdminLayout from '@/Layouts/SchoolAdminLayout.vue';
defineOptions({ layout: SchoolAdminLayout });
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import SignaturePad from '@/Components/SignaturePad.vue';
import SealUploader from '@/Components/SealUploader.vue';

const template = ref({
  name: 'Default Receipt Template',
  slogan: 'Excellence in Education',
  logo_path: null,
  logo_url: null,
  primary_font: 'Arial, sans-serif',
  secondary_font: 'Georgia, serif',
  primary_color: '#1f2937',
  secondary_color: '#3b82f6',
  accent_color: '#10b981',
  logo_width: 150,
  logo_height: 50,
  show_school_logo: true,
  show_school_slogan: true,
  show_school_address: true,
  show_school_phone: true,
  show_school_email: true,
  show_payment_method: true,
  show_receipt_number: true,
  show_payment_date: true,
  show_student_details: true,
  show_fee_breakdown: true,
  show_balance: true,
  custom_css: '',
  is_active: true,
  // Security features
  signature_path: null,
  signature_url: null,
  signature_name: '',
  signature_title: '',
  show_signature: false,
  signature_position: 'bottom-right',
  seal_path: null,
  seal_url: null,
  show_seal: false,
  seal_position: 'bottom-left',
  seal_size: 40,
  seal_style: null,
  watermark_text: '',
  show_watermark: false,
  watermark_color: '#e5e7eb',
  watermark_opacity: 0.15,
  watermark_position: 'center',
  watermark_rotation: -45,
  show_qr_code: false,
  qr_code_position: 'top-right',
  qr_code_size: 30,
  qr_code_data: '',
  show_security_features: false,
  security_text: '',
  show_verification_url: false,
  verification_url: '',
  show_unique_id: false,
  unique_id_prefix: '',
  receipt_number_format: 'SCH{year}{sequence}',
  receipt_number_start: 1,
  receipt_number_prefix: '',
});

const isLoading = ref(false);
const isSaving = ref(false);
const showSignaturePad = ref(false);
const schoolLogoUrl = ref(null);
const schoolBrandColor = ref(null);
const schoolCounty = ref(null);
const schoolSubCounty = ref(null);
const schoolAdminEmail = ref(null);
const schoolAdminPhone = ref(null);

const sealFileName = computed(() => {
  if (template.value.seal_path) {
    return template.value.seal_path.split('/').pop() || 'seal.png';
  }
  return '';
});

onMounted(() => {
  loadTemplate();
});

async function loadTemplate() {
  isLoading.value = true;
  try {
    const response = await axios.get('/school-admin/receipt-template/data');
    if (response.data.success) {
      Object.assign(template.value, response.data.template);
      // Set signature_url and seal_url if present
      if (response.data.template.signature_url) {
        template.value.signature_url = response.data.template.signature_url;
      }
      if (response.data.template.seal_url) {
        template.value.seal_url = response.data.template.seal_url;
      }
      // Set school logo url
      if (response.data.school_logo_url) {
        schoolLogoUrl.value = response.data.school_logo_url;
      } else if (response.data.template.logo_url) {
        schoolLogoUrl.value = response.data.template.logo_url;
      } else {
        schoolLogoUrl.value = null;
      }
      // Set school brand color
      if (response.data.school_brand_color) {
        schoolBrandColor.value = response.data.school_brand_color;
        template.value.accent_color = response.data.school_brand_color;
        template.value.primary_color = response.data.school_brand_color;
      }
      // Set school address/contact info
      schoolCounty.value = response.data.school_county || null;
      schoolSubCounty.value = response.data.school_sub_county || null;
      schoolAdminEmail.value = response.data.school_admin_email || null;
      schoolAdminPhone.value = response.data.school_admin_phone || null;
    }
  } catch (error) {
    console.error('Failed to load template:', error);
  } finally {
    isLoading.value = false;
  }
}

async function saveTemplate() {
  isSaving.value = true;
  try {
    const response = await axios.post('/school-admin/receipt-template', template.value);
    if (response.data.success) {
      // Show success message
      alert('Template saved successfully!');
    }
  } catch (error) {
    console.error('Failed to save template:', error);
    alert('Failed to save template. Please try again.');
  } finally {
    isSaving.value = false;
  }
}

async function handleSignatureUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('signature', file);

  try {
    const response = await axios.post('/school-admin/receipt-template/signature', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    if (response.data.success) {
      template.value.signature_url = response.data.signature_url;
      template.value.signature_path = response.data.signature_path;
    }
  } catch (error) {
    console.error('Failed to upload signature:', error);
    alert('Failed to upload signature. Please try again.');
  }
}

async function handleSealUpload(file) {
  if (!file) return;
  const formData = new FormData();
  formData.append('seal', file);
  
  // Include the current seal style when uploading
  // We need to get this from the SealUploader component
  // For now, we'll add a method to pass the style from the component
  if (file.sealStyle) {
    formData.append('seal_style', file.sealStyle);
    console.log('Uploading seal with style:', file.sealStyle);
  }
  
  try {
    const response = await axios.post('/school-admin/receipt-template/seal', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    if (response.data.success) {
      template.value.seal_url = response.data.seal_url;
      template.value.seal_path = response.data.seal_path;
      // Update the template with the seal style
      if (response.data.seal_style) {
        template.value.seal_style = response.data.seal_style;
        console.log('Seal style saved:', response.data.seal_style);
      }
    }
  } catch (error) {
    console.error('Failed to upload seal:', error);
    
    // Show detailed error message
    let errorMessage = 'Failed to upload seal. Please try again.';
    if (error.response && error.response.data) {
      if (error.response.data.message) {
        errorMessage = error.response.data.message;
      } else if (error.response.data.errors && error.response.data.errors.seal) {
        errorMessage = error.response.data.errors.seal[0];
      }
    }
    
    alert(errorMessage);
  }
}

async function handleSignatureSave(file) {
  const formData = new FormData();
  formData.append('signature', file);
  
  try {
    const response = await axios.post('/school-admin/receipt-template/signature', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    if (response.data.success) {
      template.value.signature_url = response.data.signature_url;
      template.value.signature_path = response.data.signature_path;
      showSignaturePad.value = false;
    }
  } catch (error) {
    console.error('Failed to save signature:', error);
    alert('Failed to save signature. Please try again.');
  }
}

async function removeSignature() {
  try {
    const response = await axios.delete('/school-admin/receipt-template/signature');
    if (response.data.success) {
      template.value.signature_url = null;
      template.value.signature_path = null;
    }
  } catch (error) {
    console.error('Failed to remove signature:', error);
    alert('Failed to remove signature. Please try again.');
  }
}

async function removeSeal() {
  try {
    const response = await axios.delete('/school-admin/receipt-template/seal');
    if (response.data.success) {
      template.value.seal_url = null;
      template.value.seal_path = null;
      template.value.seal_style = null;
    }
  } catch (error) {
    console.error('Failed to remove seal:', error);
    alert('Failed to remove seal. Please try again.');
  }
}
</script> 