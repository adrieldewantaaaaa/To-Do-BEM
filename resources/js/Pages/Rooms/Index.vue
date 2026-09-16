<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TdbBadge from '@/Components/UI/TdbBadge.vue';
import TdbButton from '@/Components/UI/TdbButton.vue';
import TdbIcon from '@/Components/UI/TdbIcon.vue';
import TdbInput from '@/Components/UI/TdbInput.vue';
import TdbModal from '@/Components/UI/TdbModal.vue';

defineProps({ rooms: { type: Array, default: () => [] } });

const creating = ref(false);
const joining = ref(false);
const createForm = useForm({ name: '', description: '' });
const joinForm = useForm({ invite_code: '' });

const submitCreate = () =>
  createForm.post(route('rooms.store'), {
    onSuccess: () => {
      creating.value = false;
      createForm.reset();
    },
  });

const submitJoin = () =>
  joinForm.post(route('rooms.join'), {
    onSuccess: () => {
      joining.value = false;
      joinForm.reset();
    },
  });
</script>

<template>
  <AppLayout :title="'Rooms'">
    <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
      <div>
        <h1 class="tdb-heading text-3xl sm:text-4xl">{{ 'Rooms' }}</h1>
        <p class="mt-2 text-[var(--muted)]">{{ 'Shared spaces where your group plans projects and tasks together.' }}</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <TdbButton variant="secondary" @click="joining = true"><TdbIcon name="plus" :size="18" /> {{ 'Join room' }}</TdbButton>
        <TdbButton @click="creating = true"><TdbIcon name="rooms" :size="18" /> {{ 'Create room' }}</TdbButton>
      </div>
    </div>

    <div v-if="rooms.length" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <Link v-for="room in rooms" :key="room.id" :href="route('rooms.show', room.id)" class="tdb-card group block p-5">
        <div class="flex items-start justify-between gap-3">
          <h2 class="text-lg font-bold leading-snug">{{ room.name }}</h2>
          <TdbBadge :tone="room.is_owner ? 'primary' : 'neutral'">{{ room.is_owner ? 'Owner' : 'Member' }}</TdbBadge>
        </div>
        <p class="mt-1 line-clamp-2 min-h-[2.5rem] text-sm text-[var(--muted)]">
          {{ room.description || 'No description.' }}
        </p>
        <div class="mt-4 flex items-center gap-4 border-t border-[var(--line)] pt-3 text-sm text-[var(--muted)]">
          <span class="inline-flex items-center gap-1.5"><TdbIcon name="rooms" :size="16" /> {{ room.members_count }}</span>
          <span class="inline-flex items-center gap-1.5"
            ><TdbIcon name="projects" :size="16" /> {{ room.projects_count }}</span
          >
          <span class="ml-auto font-semibold text-[var(--primary)]">{{ 'Open →' }}</span>
        </div>
      </Link>
    </div>

    <section v-else class="tdb-card px-5 py-16 text-center">
      <span class="mx-auto grid h-14 w-14 place-items-center rounded-xl border border-dashed border-[var(--line)]"
        ><TdbIcon name="rooms" :size="28"
      /></span>
      <h2 class="tdb-heading mt-4 text-2xl">{{ 'No rooms yet.' }}</h2>
      <p class="mx-auto mt-2 max-w-md text-sm text-[var(--muted)]">
        {{ 'Create a room to collaborate with your group, or join one with an invite code.' }}
      </p>
      <div class="mt-5 flex justify-center gap-2">
        <TdbButton variant="secondary" @click="joining = true">{{ 'Join with code' }}</TdbButton>
        <TdbButton @click="creating = true">{{ 'Create room' }}</TdbButton>
      </div>
    </section>

    <TdbModal :show="creating" :title="'Create a room'" @close="creating = false">
      <form class="space-y-5" @submit.prevent="submitCreate">
        <TdbInput
          id="room-name"
          v-model="createForm.name"
          :label="'Room name'"
          required
          maxlength="150"
          :placeholder="'e.g. Kelompok Mobile Programming'"
          :error="createForm.errors.name"
        />
        <div>
          <label for="room-desc" class="tdb-label">{{ 'Description' }} <span class="text-[var(--muted)]">({{ 'optional' }})</span></label>
          <textarea
            id="room-desc"
            v-model="createForm.description"
            class="tdb-input min-h-24 resize-y"
            maxlength="2000"
            :placeholder="'What is this room about?'"
          />
          <p v-if="createForm.errors.description" class="mt-1 text-sm text-[var(--danger)]">
            {{ createForm.errors.description }}
          </p>
        </div>
        <div class="flex justify-end gap-2 border-t border-[var(--line)] pt-5">
          <TdbButton variant="ghost" type="button" @click="creating = false">{{ 'Cancel' }}</TdbButton>
          <TdbButton type="submit" :disabled="createForm.processing">
            {{ createForm.processing ? 'Creating…' : 'Create room' }}
          </TdbButton>
        </div>
      </form>
    </TdbModal>

    <TdbModal :show="joining" :title="'Join a room'" @close="joining = false">
      <form class="space-y-5" @submit.prevent="submitJoin">
        <TdbInput
          id="invite-code"
          v-model="joinForm.invite_code"
          :label="'Invite code'"
          required
          maxlength="20"
          placeholder="e.g. A8F3-K9L2-X1P7"
          :error="joinForm.errors.invite_code"
        />
        <p class="text-sm text-[var(--muted)]">{{ 'Ask a room member for the invite code, then paste it here.' }}</p>
        <div class="flex justify-end gap-2 border-t border-[var(--line)] pt-5">
          <TdbButton variant="ghost" type="button" @click="joining = false">{{ 'Cancel' }}</TdbButton>
          <TdbButton type="submit" :disabled="joinForm.processing">
            {{ joinForm.processing ? 'Joining…' : 'Join room' }}
          </TdbButton>
        </div>
      </form>
    </TdbModal>
  </AppLayout>
</template>
