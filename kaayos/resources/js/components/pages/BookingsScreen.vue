<template>
  <div class="p-4 md:p-8 max-w-7xl mx-auto">
    <div class="space-y-6">
      <div>
        <h1 class="text-3xl font-bold text-[#112331]">Your Bookings</h1>
        <p class="text-[#6b95b3] mt-2">Manage your service requests</p>
      </div>

      <div class="flex gap-2 flex-wrap">
        <button v-for="f in filters" :key="f.key"
          @click="activeFilter = f.key"
          :class="['px-4 py-2 rounded-full text-sm font-medium transition-colors',
            activeFilter === f.key
              ? 'bg-[#2b516f] text-white'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
          ]">
          {{ f.label }} ({{ f.count }})
        </button>
      </div>

      <div v-if="filtered.length === 0" class="text-center py-16 text-gray-400">
        <p class="text-lg">No bookings found</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="booking in filtered" :key="booking.id" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
          <div class="flex justify-between items-start mb-4">
            <div>
              <h3 class="font-bold text-lg text-[#112331]">{{ booking.service }}</h3>
              <p class="text-sm text-[#6b95b3] mt-1">{{ booking.workerName }}</p>
            </div>
            <span :class="statusClass(booking.rawStatus)">
              {{ statusLabel(booking.rawStatus) }}
            </span>
          </div>

          <div class="space-y-2 mb-4 pb-4 border-b border-gray-100">
            <p class="text-sm text-gray-600">
              <span class="font-medium text-[#112331]">Date & Time:</span> {{ booking.date }}
            </p>
            <p class="text-sm text-gray-600">
              <span class="font-medium text-[#112331]">Location:</span> {{ booking.location }}
            </p>
            <p class="text-sm text-gray-600">
              <span class="font-medium text-[#112331]">Amount:</span> ₱{{ Number(booking.price).toLocaleString() }}
            </p>
            <p v-if="booking.notes" class="text-sm text-gray-500 italic">
              "{{ booking.notes }}"
            </p>
          </div>

          <div class="flex gap-2">
            <button v-if="canCancel(booking.rawStatus)"
              @click="confirmCancel(booking)"
              class="flex-1 py-2 border border-red-300 text-red-600 font-medium rounded-lg hover:bg-red-50 transition-colors text-sm">
              Cancel
            </button>
            <button v-if="booking.rawStatus === 'completed'"
              class="flex-1 py-2 bg-[#2b516f] text-white font-medium rounded-lg hover:bg-[#1b364d] transition-colors text-sm">
              Leave Review
            </button>
            <button v-if="booking.rawStatus !== 'cancelled' && booking.rawStatus !== 'completed'"
              class="flex-1 py-2 bg-[#2b516f] text-white font-medium rounded-lg hover:bg-[#1b364d] transition-colors text-sm">
              View Details
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showCancelModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="showCancelModal = false">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-bold text-[#112331] mb-2">Cancel Booking</h3>
        <p class="text-sm text-gray-500 mb-4">Are you sure you want to cancel {{ cancelTarget?.service }}?</p>
        <textarea v-model="cancelReason" placeholder="Reason for cancellation (optional)"
          class="w-full border border-gray-200 rounded-lg p-3 text-sm mb-4 resize-none" rows="3"></textarea>
        <div class="flex gap-2">
          <button @click="showCancelModal = false" class="flex-1 py-2 border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 text-sm">
            Keep Booking
          </button>
          <button @click="submitCancel" class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
            Yes, Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAppData } from '../../composables/useAppData';

const { bookings } = useAppData();

const STATUS_LABELS = {
  new: 'Pending',
  accepted: 'Accepted',
  en_route: 'En Route',
  in_progress: 'In Progress',
  completed: 'Completed',
  cancelled: 'Cancelled',
};

const STATUS_CLASSES = {
  new: 'bg-gray-100 text-gray-700',
  accepted: 'bg-blue-100 text-blue-700',
  en_route: 'bg-purple-100 text-purple-700',
  in_progress: 'bg-orange-100 text-orange-700',
  completed: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-700',
};

const CANCELLABLE = ['new', 'accepted', 'en_route', 'in_progress'];
const ACTIVE = ['accepted', 'en_route', 'in_progress'];

const activeFilter = ref('');
const showCancelModal = ref(false);
const cancelTarget = ref(null);
const cancelReason = ref('');

const bookingsWithRaw = computed(() =>
  bookings.value.map(b => ({
    ...b,
    rawStatus: b.rawStatus || b.status.toLowerCase().replace(' ', '_'),
  }))
);

const filters = computed(() => {
  const all = bookingsWithRaw.value;
  return [
    { key: '', label: 'All', count: all.length },
    { key: 'active', label: 'Active', count: all.filter(b => ACTIVE.includes(b.rawStatus)).length },
    { key: 'new', label: 'Pending', count: all.filter(b => b.rawStatus === 'new').length },
    { key: 'completed', label: 'Completed', count: all.filter(b => b.rawStatus === 'completed').length },
  ];
});

const filtered = computed(() => {
  if (!activeFilter.value) return bookingsWithRaw.value;
  if (activeFilter.value === 'active') return bookingsWithRaw.value.filter(b => ACTIVE.includes(b.rawStatus));
  return bookingsWithRaw.value.filter(b => b.rawStatus === activeFilter.value);
});

function statusLabel(raw) {
  return STATUS_LABELS[raw] || raw;
}

function statusClass(raw) {
  return 'px-3 py-1 rounded-full text-xs font-medium ' + (STATUS_CLASSES[raw] || 'bg-yellow-100 text-yellow-700');
}

function canCancel(raw) {
  return CANCELLABLE.includes(raw);
}

function confirmCancel(booking) {
  cancelTarget.value = booking;
  cancelReason.value = '';
  showCancelModal.value = true;
}

function submitCancel() {
  if (!cancelTarget.value) return;
  const booking = cancelTarget.value;
  fetch('/client/bookings/' + booking.id + '/cancel', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' },
    body: JSON.stringify({ reason: cancelReason.value || 'Cancelled by client' }),
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) location.reload();
    else alert(data.message || 'Failed to cancel.');
  })
  .catch(() => alert('Something went wrong.'))
  .finally(() => { showCancelModal.value = false; });
}
</script>
