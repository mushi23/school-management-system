<template>
  <div class="w-full max-w-2xl mx-auto mt-12">
    <div v-if="onHoliday" class="bg-blue-50 dark:bg-blue-900/40 border border-blue-200 dark:border-blue-700 rounded-2xl shadow-lg dark:shadow-blue-950/40 p-8 flex flex-col items-center justify-center">
      <i class="fas fa-umbrella-beach text-4xl text-blue-400 mb-4"></i>
      <h2 class="text-2xl font-bold text-blue-700 dark:text-blue-200 mb-2">You are currently on holiday!</h2>
      <p class="text-gray-700 dark:text-gray-200">There is no active school term at the moment. Enjoy your break!</p>
    </div>
    <div v-else class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-lg dark:shadow-blue-950/40 p-8">
      <h2 class="text-2xl font-bold text-blue-700 dark:text-blue-200 mb-2">Fee Structure for {{ structureTitle }}</h2>
      <div v-if="currentTerm">
        <div class="flex items-center mb-4 gap-4">
          <span class="inline-block bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-200 rounded px-3 py-1 text-sm font-semibold mr-2">
            {{ currentTerm.name }}
          </span>
          <span class="text-gray-500 dark:text-gray-300 text-sm">
            ({{ currentTerm.start_date }} to {{ currentTerm.end_date }})
          </span>
        </div>
        <table class="min-w-full table-auto border-collapse border border-gray-300 dark:border-gray-800 rounded-xl overflow-hidden">
          <thead class="bg-gray-100 dark:bg-gray-900">
            <tr>
              <th class="border border-gray-300 dark:border-gray-800 px-4 py-2 text-left text-gray-800 dark:text-gray-100">Fee Item</th>
              <th class="border border-gray-300 dark:border-gray-800 px-4 py-2 text-right text-gray-800 dark:text-gray-100">Amount (KES)</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(fee, idx) in fees"
              :key="idx"
              class="hover:bg-gray-50 dark:hover:bg-gray-900 transition"
            >
              <td class="border border-gray-300 dark:border-gray-800 px-4 py-2 font-semibold text-gray-900 dark:text-gray-100">{{ fee.name }}</td>
              <td class="border border-gray-300 dark:border-gray-800 px-4 py-2 text-right text-gray-900 dark:text-gray-100">{{ fee.price ? fee.price.toLocaleString() : '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="currentTerm" class="flex flex-col items-center mt-8">
        <div class="flex flex-col items-center gap-6 w-full justify-center">
          <div class="flex items-center gap-4">
            <span class="inline-flex items-center px-2 py-1 rounded text-sm bg-yellow-100 text-yellow-700 dark:bg-yellow-800 dark:text-yellow-200 font-semibold">
              <i class="fas fa-coins mr-2"></i>
              Fee Balance: Ksh {{ feeBalance?.toLocaleString?.() ?? feeBalance }}
            </span>
            
            <!-- Receipts Link -->
            <Link
              href="/student/receipts"
              class="inline-flex items-center px-3 py-1 rounded text-sm bg-blue-100 text-blue-700 dark:bg-blue-800 dark:text-blue-200 font-semibold hover:bg-blue-200 dark:hover:bg-blue-700 transition-colors duration-200"
            >
              <i class="fas fa-receipt mr-2"></i>
              View Receipts
            </Link>
          </div>
          
          <!-- Amount Input Section -->
          <div class="w-full max-w-md">
            <label for="payment-amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Payment Amount (KES)
            </label>
            <div class="relative mb-3">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400">Ksh</span>
              <input
                id="payment-amount"
                v-model="paymentAmount"
                type="number"
                min="1"
                :max="feeBalance"
                step="1"
                class="w-full pl-12 pr-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter amount"
                @input="validateAmount"
              />
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
              Maximum: Ksh {{ feeBalance?.toLocaleString?.() ?? feeBalance }}
            </p>
            
            <!-- Quick Amount Buttons -->
            <div v-if="quickAmounts.length > 0" class="mb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Quick Amounts
              </label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  v-for="amount in quickAmounts"
                  :key="amount"
                  @click="paymentAmount = amount"
                  :class="[
                    'px-3 py-2 text-sm rounded-lg border transition-colors duration-200',
                    paymentAmount === amount
                      ? 'bg-blue-100 dark:bg-blue-800 border-blue-300 dark:border-blue-600 text-blue-700 dark:text-blue-200'
                      : 'bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                  ]"
                >
                  Ksh {{ amount.toLocaleString() }}
                </button>
              </div>
            </div>
            
            <!-- Amount Error -->
            <div v-if="amountError" class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-3">
              <p class="text-sm text-red-700 dark:text-red-300">{{ amountError }}</p>
            </div>
          </div>
          
          <!-- Pay Button -->
          <button 
            @click="openPaymentModal" 
            :disabled="isProcessing || paymentStatus === 'pending' || !isAmountValid"
            :class="['px-6 py-2 text-white rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-blue-400', getPaymentButtonClass()]"
          >
            {{ getPaymentButtonText() }}
          </button>
        </div>
      </div>

      <!-- Payment Modal -->
      <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="showPaymentModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4" @click.stop>
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Make Payment</h3>
              <button
                @click="showPaymentModal = false"
                class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors duration-200"
              >
                <i class="fas fa-times text-xl"></i>
              </button>
            </div>
          </div>
          
          <div class="p-6 space-y-4">
            <!-- Payment Summary -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-4">
              <h4 class="text-sm font-medium text-blue-700 dark:text-blue-300 mb-2">Payment Summary</h4>
              <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                  <span class="text-blue-600 dark:text-blue-400">Amount:</span>
                  <span class="font-medium text-blue-900 dark:text-blue-100">Ksh {{ (paymentAmount || 0).toLocaleString() }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-blue-600 dark:text-blue-400">Remaining Balance:</span>
                  <span class="font-medium text-blue-900 dark:text-blue-100">Ksh {{ (feeBalance - (paymentAmount || 0)).toLocaleString() }}</span>
                </div>
              </div>
            </div>

            <!-- Phone Number Input -->
            <div>
              <label for="phone-number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                M-PESA Phone Number
              </label>
              <input
                id="phone-number"
                v-model="phoneNumber"
                type="tel"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="2547XXXXXXXX"
                @input="formatPhoneNumber"
              />
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Format: 2547XXXXXXXX (e.g., 254712345678)
              </p>
            </div>

            <!-- Error Message -->
            <div v-if="paymentError" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-3">
              <p class="text-sm text-red-700 dark:text-red-300">{{ paymentError }}</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-4">
              <button
                @click="showPaymentModal = false"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Cancel
              </button>
              <button
                @click="handlePayment"
                :disabled="!isPhoneValid || isProcessing"
                class="px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <i v-if="isProcessing" class="fas fa-spinner fa-spin mr-2"></i>
                {{ isProcessing ? 'Processing...' : 'Pay Now' }}
              </button>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="text-gray-500 dark:text-gray-300 mt-4">No fee information available for the current term.</div>
    </div>
  </div>
</template>

<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Link } from '@inertiajs/vue3';
defineOptions({ layout: StudentLayout });
import { ref, computed } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const student = page.props.student;

const props = defineProps({
  currentTerm: Object,
  onHoliday: Boolean,
  fees: Array,
  structureTitle: String,
  feeBalance: Number,
});

const isProcessing = ref(false);
const paymentStatus = ref(null);
const checkoutRequestId = ref(null);
const showPaymentModal = ref(false);
const paymentAmount = ref(props.feeBalance || null);
const phoneNumber = ref('');
const paymentError = ref('');
const amountError = ref('');

// Quick amount options - dynamically generated based on fee balance
const quickAmounts = computed(() => {
  const balance = props.feeBalance || 0;
  const amounts = [];
  
  // Add common amounts
  if (balance >= 1000) amounts.push(1000);
  if (balance >= 2000) amounts.push(2000);
  if (balance >= 5000) amounts.push(5000);
  if (balance >= 10000) amounts.push(10000);
  if (balance >= 20000) amounts.push(20000);
  if (balance >= 50000) amounts.push(50000);
  
  // Add fee balance as an option
  if (balance > 0 && !amounts.includes(balance)) {
    amounts.push(balance);
  }
  
  // Add half of fee balance if it's significant
  const halfBalance = Math.floor(balance / 2);
  if (halfBalance >= 1000 && !amounts.includes(halfBalance)) {
    amounts.push(halfBalance);
  }
  
  // Sort and return unique amounts
  return [...new Set(amounts)].sort((a, b) => a - b);
});

// Validation and formatting functions
function validateAmount() {
  amountError.value = '';
  
  if (!paymentAmount.value) {
    amountError.value = 'Please enter a payment amount';
    return false;
  }
  
  if (paymentAmount.value < 1) {
    amountError.value = 'Amount must be at least Ksh 1';
    return false;
  }
  
  if (paymentAmount.value > props.feeBalance) {
    amountError.value = `Amount cannot exceed your fee balance of Ksh ${props.feeBalance.toLocaleString()}`;
    return false;
  }
  
  return true;
}

function formatPhoneNumber() {
  // Remove all non-digit characters
  let cleaned = phoneNumber.value.replace(/\D/g, '');
  
  // If it starts with 0, replace with 254
  if (cleaned.startsWith('0')) {
    cleaned = '254' + cleaned.substring(1);
  }
  
  // If it starts with 7, add 254 prefix
  if (cleaned.startsWith('7') && cleaned.length === 9) {
    cleaned = '254' + cleaned;
  }
  
  phoneNumber.value = cleaned;
}

// Computed properties for validation
const isAmountValid = computed(() => {
  return paymentAmount.value && 
         paymentAmount.value >= 1 && 
         paymentAmount.value <= props.feeBalance;
});

const isPhoneValid = computed(() => {
  return phoneNumber.value && 
         phoneNumber.value.match(/^2547\d{8}$/);
});

async function handlePayment() {
  if (!isPhoneValid.value || isProcessing.value) return;
  
  // Validate phone number
  if (!phoneNumber.value.match(/^2547\d{8}$/)) {
    paymentError.value = 'Please enter a valid M-PESA phone number in format: 2547XXXXXXXX';
    return;
  }

  isProcessing.value = true;
  paymentStatus.value = 'initiating';
  paymentError.value = '';

  try {
    const response = await axios.post('/api/mpesa/initiate-payment', {
      amount: paymentAmount.value,
      phone_number: phoneNumber.value,
      student_id: student?.id,
      term: props.currentTerm?.name
    });

    if (response.data.success) {
      checkoutRequestId.value = response.data.checkout_request_id;
      paymentStatus.value = 'pending';
      showPaymentModal.value = false; // Close modal
      
      // Check if this is sandbox mode
      if (response.data.sandbox_mode) {
        alert('Sandbox Mode: Payment initiated successfully! Since this is a test environment, you can simulate payment completion using the test endpoint.');
        
        // For sandbox, provide option to simulate payment completion
        if (confirm('Would you like to simulate payment completion for testing?')) {
          await simulateSandboxPayment();
        }
      } else {
        alert('Payment prompt sent to your phone. Please check your M-PESA app and enter your PIN to complete the payment.');
        // Start polling for payment status
        pollPaymentStatus();
      }
    } else {
      paymentError.value = 'Failed to initiate payment: ' + response.data.message;
      paymentStatus.value = 'failed';
    }
  } catch (error) {
    console.error('Payment initiation error:', error);
    paymentError.value = 'An error occurred while initiating payment. Please try again.';
    paymentStatus.value = 'failed';
  } finally {
    isProcessing.value = false;
  }
}

// Open payment modal (amount should already be set)
function openPaymentModal() {
  if (!isAmountValid.value) {
    amountError.value = 'Please enter a valid payment amount first';
    return;
  }
  
  showPaymentModal.value = true;
  phoneNumber.value = '';
  paymentError.value = '';
}

async function simulateSandboxPayment() {
  if (!checkoutRequestId.value) return;
  
  try {
    const response = await axios.post('/api/mpesa/simulate-sandbox-payment', {
      checkout_request_id: checkoutRequestId.value
    });
    
    if (response.data.success) {
      paymentStatus.value = 'completed';
      alert('Sandbox payment simulated successfully! Your fee balance has been updated.');
      // Refresh the page to show updated balance
      window.location.reload();
    } else {
      alert('Failed to simulate payment: ' + response.data.message);
      paymentStatus.value = 'failed';
    }
  } catch (error) {
    console.error('Sandbox payment simulation error:', error);
    alert('An error occurred while simulating payment. Please try again.');
    paymentStatus.value = 'failed';
  }
}

async function pollPaymentStatus() {
  if (!checkoutRequestId.value) return;

  const maxAttempts = 30; // 5 minutes with 10-second intervals
  let attempts = 0;

  const poll = async () => {
    try {
      const response = await axios.get('/api/mpesa/check-status', {
        params: { checkout_request_id: checkoutRequestId.value }
      });

      if (response.data.success) {
        const status = response.data.status;
        
        if (status === 'completed') {
          paymentStatus.value = 'completed';
          alert('Payment completed successfully! Your fee balance has been updated.');
          // Refresh the page to show updated balance
          window.location.reload();
          return;
        } else if (status === 'failed') {
          paymentStatus.value = 'failed';
          alert('Payment failed: ' + (response.data.result_desc || 'Unknown error'));
          return;
        }
      }

      attempts++;
      if (attempts < maxAttempts) {
        setTimeout(poll, 10000); // Poll every 10 seconds
      } else {
        paymentStatus.value = 'timeout';
        alert('Payment status check timed out. Please check your M-PESA app or contact support.');
      }
    } catch (error) {
      console.error('Payment status check error:', error);
      attempts++;
      if (attempts < maxAttempts) {
        setTimeout(poll, 10000);
      }
    }
  };

  poll();
}

function getPaymentButtonText() {
  if (isProcessing.value) return 'Processing...';
  if (paymentStatus.value === 'pending') return 'Payment Pending...';
  if (paymentStatus.value === 'completed') return 'Payment Completed!';
  if (paymentStatus.value === 'failed') return 'Payment Failed - Try Again';
  return 'Pay';
}

function getPaymentButtonClass() {
  if (paymentStatus.value === 'completed') return 'bg-green-600 hover:bg-green-700';
  if (paymentStatus.value === 'failed') return 'bg-red-600 hover:bg-red-700';
  return 'bg-blue-600 hover:bg-blue-700';
}
</script> 