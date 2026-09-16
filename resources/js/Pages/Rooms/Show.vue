<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TdbBadge from '@/Components/UI/TdbBadge.vue';
import TdbButton from '@/Components/UI/TdbButton.vue';
import TdbIcon from '@/Components/UI/TdbIcon.vue';
import TdbInput from '@/Components/UI/TdbInput.vue';
import TdbModal from '@/Components/UI/TdbModal.vue';
import ProjectProgress from '@/Components/Project/ProjectProgress.vue';
import { formatDate } from '@/lib/format';

const props = defineProps({
  room: { type: Object, required: true },
  projects: { type: Array, default: () => [] },
  canManage: { type: Boolean, default: false },
});

const copied = ref(false);
const confirmingDelete = ref(false);
const confirmingLeave = ref(false);
const removingMember = ref(null);
const creatingProject = ref(false);

const projectForm = useForm({ name: '', description: '', deadline: '', status: 'active' });
const submitProject = () =>
  projectForm.post(route('rooms.projects.store', props.room.id), {
    onSuccess: () => {
      creatingProject.value = false;
      projectForm.reset();
    },
  });

const copyCode = async () => {
  try {
    await navigator.clipboard.writeText(props.room.invite_code);
    copied.value = true;
    setTimeout(() => (copied.value = false), 1800);
  } catch (e) {
    /* clipboard blocked — user can still select the code manually */
  }
};

const deleteRoom = () => router.delete(route('rooms.destroy', props.room.id));
const leaveRoom = () => router.delete(route('rooms.leave', props.room.id));
const removeMember = () => {
  router.delete(route('rooms.members.remove', [props.room.id, removingMember.value.id]), {
    preserveScroll: true,
    onFinish: () => (removingMember.value = null),
  });
};
</script>

<template>
  <AppLayout :title="room.name">
    <div class="mb-6">
      <Link :href="route('rooms.index')" class="text-sm font-bold text-[var(--primary)]">← Rooms</Link>
      <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
        <div>
          <div class="flex flex-wrap items-center gap-3">
            <h1 class="tdb-heading text-3xl sm:text-4xl">{{ room.name }}</h1>
            <TdbBadge :tone="canManage ? 'primary' : 'neutral'">{{ canManage ? 'Owner' : 'Member' }}</TdbBadge>
          </div>
          <p class="mt-2 max-w-3xl text-[var(--muted)]">{{ room.description || 'No description yet.' }}</p>
          <p class="mt-2 text-sm text-[var(--muted)]">
            Owner <strong class="text-[var(--ink)]">{{ room.owner.name }}</strong> · created {{ formatDate(room.created_at) }}
          </p>
        </div>
        <div class="flex flex-wrap gap-2">
          <TdbButton v-if="canManage" variant="secondary" @click="confirmingDelete = true">
            <TdbIcon name="trash" :size="17" /> Delete room
          </TdbButton>
          <TdbButton v-else variant="secondary" @click="confirmingLeave = true">
            <TdbIcon name="logout" :size="17" /> Leave room
          </TdbButton>
        </div>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
      <!-- Projects -->
      <section>
        <div class="mb-3 flex items-center justify-between">
          <h2 class="tdb-heading text-2xl">Projects</h2>
          <TdbButton v-if="projects.length" @click="creatingProject = true"
            ><TdbIcon name="plus" :size="18" /> Add project</TdbButton
          >
        </div>
        <div v-if="projects.length" class="grid gap-4 sm:grid-cols-2">
          <Link
            v-for="project in projects"
            :key="project.id"
            :href="route('projects.show', project.id)"
            class="tdb-card group block p-5"
          >
            <div class="flex items-center justify-between gap-2">
              <h3 class="font-bold">{{ project.name }}</h3>
              <TdbBadge tone="neutral">{{ project.status }}</TdbBadge>
            </div>
            <p class="mt-1 line-clamp-2 text-sm text-[var(--muted)]">{{ project.description || 'No description.' }}</p>
            <div class="mt-4"><ProjectProgress :progress="project.progress" /></div>
          </Link>
        </div>
        <div v-else class="tdb-card px-5 py-14 text-center">
          <span class="mx-auto grid h-12 w-12 place-items-center rounded-xl border border-dashed border-[var(--line)]"
            ><TdbIcon name="projects" :size="24"
          /></span>
          <h3 class="tdb-heading mt-3 text-xl">No projects yet.</h3>
          <p class="mx-auto mt-1 max-w-sm text-sm text-[var(--muted)]">
            Create a shared project so the group can plan tasks together.
          </p>
          <TdbButton class="mt-4" @click="creatingProject = true"
            ><TdbIcon name="plus" :size="18" /> Create project</TdbButton
          >
        </div>
      </section>

      <!-- Sidebar: invite + members -->
      <aside class="space-y-6">
        <section class="tdb-card p-5">
          <h2 class="text-sm font-bold uppercase tracking-[.08em] text-[var(--muted)]">Invite code</h2>
          <div class="mt-3 flex items-center gap-2">
            <code class="flex-1 rounded-lg border border-[var(--line)] bg-[var(--surface-2)] px-3 py-2 text-lg font-bold tracking-[.18em]">{{ room.invite_code }}</code>
            <button
              class="tdb-btn tdb-btn-secondary !min-h-10 !px-3"
              :aria-label="copied ? 'Copied' : 'Copy invite code'"
              @click="copyCode"
            >
              <TdbIcon :name="copied ? 'check' : 'copy'" :size="18" />
            </button>
          </div>
          <p class="mt-2 text-xs text-[var(--muted)]">Share this code so others can join the room.</p>
        </section>

        <section class="tdb-card overflow-hidden">
          <div class="flex items-center justify-between border-b border-[var(--line)] px-5 py-3">
            <h2 class="text-sm font-bold uppercase tracking-[.08em] text-[var(--muted)]">Members</h2>
            <span class="text-sm font-bold">{{ room.members.length }}</span>
          </div>
          <ul class="divide-y divide-[var(--line)]">
            <li v-for="member in room.members" :key="member.id" class="flex items-center gap-3 px-5 py-3">
              <span class="grid h-9 w-9 flex-none place-items-center rounded-full bg-[var(--surface-2)] text-sm font-bold">
                {{ member.name.charAt(0).toUpperCase() }}
              </span>
              <div class="min-w-0 flex-1">
                <strong class="block truncate text-sm">{{ member.name }}</strong>
                <span class="block truncate text-xs text-[var(--muted)]">{{ member.email }}</span>
              </div>
              <TdbBadge :tone="member.role === 'owner' ? 'primary' : 'neutral'">{{ member.role }}</TdbBadge>
              <button
                v-if="canManage && member.role !== 'owner'"
                class="tdb-btn tdb-btn-ghost !min-h-8 !px-2 text-[var(--danger)]"
                aria-label="Remove member"
                @click="removingMember = member"
              >
                <TdbIcon name="close" :size="16" />
              </button>
            </li>
          </ul>
        </section>
      </aside>
    </div>

    <TdbModal :show="creatingProject" title="Create a project" @close="creatingProject = false">
      <form class="space-y-5" @submit.prevent="submitProject">
        <TdbInput
          id="rp-name"
          v-model="projectForm.name"
          label="Project name"
          required
          maxlength="150"
          placeholder="e.g. Aplikasi To-Do"
          :error="projectForm.errors.name"
        />
        <div>
          <label for="rp-desc" class="tdb-label">Description <span class="text-[var(--muted)]">(optional)</span></label>
          <textarea
            id="rp-desc"
            v-model="projectForm.description"
            class="tdb-input min-h-24 resize-y"
            maxlength="2000"
            placeholder="What is this project about?"
          />
          <p v-if="projectForm.errors.description" class="mt-1 text-sm text-[var(--danger)]">
            {{ projectForm.errors.description }}
          </p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
          <TdbInput
            id="rp-deadline"
            v-model="projectForm.deadline"
            label="Deadline"
            type="date"
            required
            :error="projectForm.errors.deadline"
          />
          <div>
            <label for="rp-status" class="tdb-label">Status</label>
            <select id="rp-status" v-model="projectForm.status" class="tdb-input">
              <option value="active">Active</option>
              <option value="completed">Completed</option>
              <option value="archived">Archived</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end gap-2 border-t border-[var(--line)] pt-5">
          <TdbButton variant="ghost" type="button" @click="creatingProject = false">Cancel</TdbButton>
          <TdbButton type="submit" :disabled="projectForm.processing">
            {{ projectForm.processing ? 'Creating…' : 'Create project' }}
          </TdbButton>
        </div>
      </form>
    </TdbModal>

    <TdbModal :show="confirmingDelete" title="Delete this room?" @close="confirmingDelete = false">
      <p class="text-[var(--muted)]">This removes the room, its projects, and all tasks inside it. This cannot be undone.</p>
      <div class="mt-6 flex justify-end gap-2">
        <TdbButton variant="ghost" @click="confirmingDelete = false">Cancel</TdbButton>
        <TdbButton variant="danger" @click="deleteRoom">Delete room</TdbButton>
      </div>
    </TdbModal>

    <TdbModal :show="confirmingLeave" title="Leave this room?" @close="confirmingLeave = false">
      <p class="text-[var(--muted)]">You will lose access to this room's projects and tasks until you rejoin with the code.</p>
      <div class="mt-6 flex justify-end gap-2">
        <TdbButton variant="ghost" @click="confirmingLeave = false">Cancel</TdbButton>
        <TdbButton variant="danger" @click="leaveRoom">Leave room</TdbButton>
      </div>
    </TdbModal>

    <TdbModal :show="!!removingMember" title="Remove member?" @close="removingMember = null">
      <p class="text-[var(--muted)]">
        Remove <strong class="text-[var(--ink)]">{{ removingMember?.name }}</strong> from this room?
      </p>
      <div class="mt-6 flex justify-end gap-2">
        <TdbButton variant="ghost" @click="removingMember = null">Cancel</TdbButton>
        <TdbButton variant="danger" @click="removeMember">Remove</TdbButton>
      </div>
    </TdbModal>
  </AppLayout>
</template>
