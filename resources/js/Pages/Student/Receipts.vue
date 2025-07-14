<template>
  <div class="w-full max-w-6xl mx-auto mt-12">
    <div class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-lg dark:shadow-blue-950/40 p-8">
      <div class="flex items-center justify-between mb-8">
        <div>
          <h2 class="text-2xl font-bold text-blue-700 dark:text-blue-200 mb-2">Payment Receipts</h2>
          <p class="text-gray-600 dark:text-gray-300">View and download your payment receipts</p>
        </div>
        <div class="flex items-center gap-4">
          <button
            @click="refreshReceipts"
            :disabled="isLoading"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 disabled:opacity-50"
          >
            <i v-if="isLoading" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-sync-alt mr-2"></i>
            Refresh
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-12">
        <div class="text-center">
          <i class="fas fa-spinner fa-spin text-4xl text-blue-500 mb-4"></i>
          <p class="text-gray-600 dark:text-gray-300">Loading receipts...</p>
        </div>
      </div>

      <!-- No Receipts State -->
      <div v-else-if="!receipts || Object.keys(receipts).length === 0" class="text-center py-12">
        <i class="fas fa-receipt text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-2">No Receipts Found</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6">
          You haven't made any payments yet. Receipts will appear here after successful payments.
        </p>
        <Link
          href="/student/fees"
          class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200"
        >
          <i class="fas fa-credit-card mr-2"></i>
          Make a Payment
        </Link>
      </div>

      <!-- Receipts List -->
      <div v-else-if="receipts && Object.keys(receipts).length > 0" class="space-y-8">
        <div
          v-for="(termReceipts, termKey) in receipts"
          :key="termKey"
          class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden"
        >
          <!-- Term Header -->
          <div class="bg-gray-50 dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ termKey }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                  {{ termReceipts.length }} receipt{{ termReceipts.length !== 1 ? 's' : '' }}
                </p>
              </div>
              <div class="text-right">
                <div class="text-sm text-gray-600 dark:text-gray-300">Total Paid</div>
                <div class="text-lg font-bold text-green-600 dark:text-green-400">
                  KES {{ getTermTotal(termReceipts).toLocaleString() }}
                </div>
              </div>
            </div>
          </div>

          <!-- Receipts in Term -->
          <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <div
              v-for="receipt in termReceipts"
              :key="receipt.id"
              class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-200"
            >
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <div class="flex items-center gap-4 mb-2">
                    <div class="flex items-center gap-2">
                      <i class="fas fa-receipt text-blue-500"></i>
                      <span class="font-mono text-sm text-gray-600 dark:text-gray-300">
                        {{ receipt.receipt_number }}
                      </span>
                    </div>
                    <span
                      :class="[
                        'px-2 py-1 text-xs font-medium rounded-full',
                        receipt.payment_method === 'mpesa' 
                          ? 'bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200'
                          : 'bg-blue-100 text-blue-700 dark:bg-blue-800 dark:text-blue-200'
                      ]"
                    >
                      {{ receipt.payment_method.toUpperCase() }}
                    </span>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                      <span class="text-gray-500 dark:text-gray-400">Amount:</span>
                      <span class="font-semibold text-gray-900 dark:text-gray-100 ml-2">
                        KES {{ receipt.amount_paid.toLocaleString() }}
                      </span>
                    </div>
                    <div>
                      <span class="text-gray-500 dark:text-gray-400">Date:</span>
                      <span class="text-gray-900 dark:text-gray-100 ml-2">
                        {{ formatDate(receipt.payment_date) }}
                      </span>
                    </div>
                    <div>
                      <span class="text-gray-500 dark:text-gray-400">Reference:</span>
                      <span class="font-mono text-gray-900 dark:text-gray-100 ml-2">
                        {{ receipt.payment_reference || 'N/A' }}
                      </span>
                    </div>
                  </div>

                  <!-- Fee Breakdown -->
                  <div v-if="receipt.fee_breakdown && receipt.fee_breakdown.length > 0" class="mt-3">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Fee Breakdown:</div>
                    <div class="space-y-1">
                      <div
                        v-for="(item, index) in receipt.fee_breakdown"
                        :key="index"
                        class="text-sm text-gray-600 dark:text-gray-300"
                      >
                        • {{ item.item }}: KES {{ item.amount.toLocaleString() }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2 ml-4">
                  <button
                    @click="viewReceipt(receipt)"
                    :disabled="isViewingReceipt === receipt.id"
                    class="px-3 py-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-200 disabled:opacity-50"
                    title="View Receipt"
                  >
                    <i v-if="isViewingReceipt === receipt.id" class="fas fa-spinner fa-spin"></i>
                    <i v-else class="fas fa-eye"></i>
                  </button>
                  
                  <button
                    @click="downloadReceipt(receipt)"
                    :disabled="isDownloadingReceipt === receipt.id"
                    class="px-3 py-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors duration-200 disabled:opacity-50"
                    title="Download PDF"
                  >
                    <i v-if="isDownloadingReceipt === receipt.id" class="fas fa-spinner fa-spin"></i>
                    <i v-else class="fas fa-download"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Receipt Preview Modal -->
    <div v-if="showReceiptModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="closeReceiptModal">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden" @click.stop>
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              Receipt Preview - {{ selectedReceipt?.receipt_number }}
            </h3>
            <button
              @click="closeReceiptModal"
              class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors duration-200"
            >
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
        </div>
        
        <div class="p-6 overflow-auto max-h-[calc(90vh-120px)]">
          <div v-if="receiptPdfUrl" class="w-full">
            <iframe
              :src="receiptPdfUrl"
              class="w-full h-96 border border-gray-200 dark:border-gray-600 rounded-lg"
              frameborder="0"
            ></iframe>
          </div>
          <div v-else class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-500 mb-4"></i>
            <p class="text-gray-600 dark:text-gray-300">Loading receipt...</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Link } from '@inertiajs/vue3';
defineOptions({ layout: StudentLayout });
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Get receipts from Inertia props
const props = defineProps({
  receipts: {
    type: Object,
    default: () => ({})
  }
});

const receipts = ref(props.receipts || {});

// Debug: Log the receipts data
console.log('Receipts component mounted with props:', props);
console.log('Receipts data:', receipts.value);
console.log('Receipts keys:', Object.keys(receipts.value));
console.log('Receipts length:', Object.keys(receipts.value).length);

const isLoading = ref(false);
const isViewingReceipt = ref(null);
const isDownloadingReceipt = ref(null);
const showReceiptModal = ref(false);
const selectedReceipt = ref(null);
const receiptPdfUrl = ref(null);

// No need to load receipts on mount since they come from props
// onMounted(() => {
//   loadReceipts();
// });

async function loadReceipts() {
  isLoading.value = true;
  try {
    // Reload the page to get fresh data
    window.location.reload();
  } catch (error) {
    console.error('Failed to load receipts:', error);
  } finally {
    isLoading.value = false;
  }
}

async function refreshReceipts() {
  await loadReceipts();
}

async function viewReceipt(receipt) {
  isViewingReceipt.value = receipt.id;
  selectedReceipt.value = receipt;
  showReceiptModal.value = true;
  receiptPdfUrl.value = null;
  
  try {
    // Open PDF in new window/tab
    const response = await axios.get(`/api/receipts/${receipt.id}/view`, {
      responseType: 'blob'
    });
    
    // Create blob URL for the PDF
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);
    receiptPdfUrl.value = url;
  } catch (error) {
    console.error('Failed to load receipt:', error);
    alert('Failed to load receipt. Please try again.');
  } finally {
    isViewingReceipt.value = null;
  }
}

async function downloadReceipt(receipt) {
  isDownloadingReceipt.value = receipt.id;
  
  try {
    const response = await axios.get(`/api/receipts/${receipt.id}/download`, {
      responseType: 'blob'
    });
    
    // Create download link
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `receipt-${receipt.receipt_number}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Failed to download receipt:', error);
  } finally {
    isDownloadingReceipt.value = null;
  }
}

function closeReceiptModal() {
  showReceiptModal.value = false;
  selectedReceipt.value = null;
  receiptPdfUrl.value = null;
}

function getTermTotal(termReceipts) {
  return termReceipts.reduce((total, receipt) => total + receipt.amount_paid, 0);
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
}
</script> 