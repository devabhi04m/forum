<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useForumStore } from '../stores/forum';
import { useAuthStore } from '../../auth/stores/auth';
import moderationApi from '../services/moderationApi';
import PostCard from '../components/PostCard.vue';
import ReplyBox from '../components/ReplyBox.vue';
import ReportModal from '../components/ReportModal.vue';
import VoteButtons from '../components/VoteButtons.vue';
import UserAvatar from '../../../components/UserAvatar.vue';
import { timeAgo } from '../../../utils/date';

const route = useRoute();
const router = useRouter();
const forum = useForumStore();
const auth = useAuthStore();

const isOwner = computed(() => auth.user && forum.currentThread?.user?.id === auth.user.id);
const canEdit = computed(() => isOwner.value && !forum.currentThread?.is_locked);

async function load() {
    forum.currentThread = null;
    forum.posts = [];
    await forum.fetchThread(route.params.slug);
    if (forum.currentThread) {
        await forum.fetchPosts(forum.currentThread.slug);
    }
}

async function sendReply(content) {
    await forum.createPost(forum.currentThread.slug, { content });
}

function onVote(vote) {
    forum.voteThread(forum.currentThread.slug, vote);
}

function requireAuth() {
    if (auth.isAuthenticated) return true;
    router.push({ name: 'auth.login', query: { redirect: route.fullPath } });
    return false;
}

function onBookmark() {
    if (!requireAuth()) return;
    forum.toggleBookmark(forum.currentThread.slug);
}

function onFollow() {
    if (!requireAuth()) return;
    forum.toggleFollow(forum.currentThread.slug);
}

const reportOpen = ref(false);
const reportTarget = ref(null);

function openReport(target) {
    if (!requireAuth()) return;
    reportTarget.value = target;
    reportOpen.value = true;
}

async function modToggle(kind) {
    const slug = forum.currentThread.slug;
    const calls = {
        pin: moderationApi.togglePin,
        lock: moderationApi.toggleLock,
        hide: moderationApi.toggleHide,
    };
    const { data } = await calls[kind](slug);
    const result = data.data ?? data;

    if (kind === 'pin') forum.currentThread.is_pinned = result.is_pinned;
    if (kind === 'lock') forum.currentThread.is_locked = result.is_locked;
    if (kind === 'hide') forum.currentThread.status = result.status;
}

async function onDelete() {
    if (!confirm('Delete this thread? This cannot be undone.')) return;
    await forum.deleteThread(forum.currentThread.id);
    router.push({ name: 'forum.home' });
}

onMounted(load);
watch(() => route.params.slug, load);
</script>

<template>
    <div class="mx-auto max-w-4xl px-4 py-8">
        <router-link
            v-if="forum.currentThread?.category"
            :to="{ name: 'categories.show', params: { slug: forum.currentThread.category.slug } }"
            class="text-sm text-ink-500 transition hover:text-brand-600"
        >
            ← {{ forum.currentThread.category.name }}
        </router-link>

        <p v-if="forum.error" class="alert-error mt-4">{{ forum.error }}</p>

        <div v-if="!forum.currentThread && forum.loading" class="mt-4 space-y-3">
            <div class="h-8 w-2/3 animate-pulse rounded bg-ink-100"></div>
            <div class="h-32 animate-pulse rounded-xl bg-ink-100"></div>
        </div>

        <template v-else-if="forum.currentThread">
            <div class="mt-3 mb-5">
                <div class="flex flex-wrap items-center gap-2">
                    <span v-if="forum.currentThread.is_pinned" class="rounded bg-amber-100 px-1.5 py-0.5 text-[11px] font-semibold text-amber-700">Pinned</span>
                    <span v-if="forum.currentThread.is_locked" class="rounded bg-ink-100 px-1.5 py-0.5 text-[11px] font-semibold text-ink-500">Locked</span>
                    <span v-if="forum.currentThread.status === 'hidden'" class="rounded bg-red-100 px-1.5 py-0.5 text-[11px] font-semibold text-red-700">Hidden</span>
                    <h1 class="text-2xl font-semibold tracking-tight text-ink-900">{{ forum.currentThread.title }}</h1>
                </div>
                <div v-if="auth.isModerator" class="mt-2 flex gap-2">
                    <button type="button" class="chip !text-xs" @click="modToggle('pin')">
                        {{ forum.currentThread.is_pinned ? 'Unpin' : 'Pin' }}
                    </button>
                    <button type="button" class="chip !text-xs" @click="modToggle('lock')">
                        {{ forum.currentThread.is_locked ? 'Unlock' : 'Lock' }}
                    </button>
                    <button type="button" class="chip !text-xs" @click="modToggle('hide')">
                        {{ forum.currentThread.status === 'hidden' ? 'Unhide' : 'Hide' }}
                    </button>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-ink-500">
                    <span>{{ forum.currentThread.views_count ?? 0 }} {{ (forum.currentThread.views_count ?? 0) === 1 ? 'view' : 'views' }}</span>
                    <span aria-hidden="true">·</span>
                    <span>{{ forum.currentThread.replies_count ?? 0 }} {{ (forum.currentThread.replies_count ?? 0) === 1 ? 'reply' : 'replies' }}</span>
                    <router-link
                        v-for="tag in forum.currentThread.tags ?? []"
                        :key="tag.id"
                        :to="{ name: 'search', query: { tag: tag.slug } }"
                        class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-600 transition hover:bg-brand-100"
                    >
                        {{ tag.name }}
                    </router-link>
                </div>
            </div>

            <article class="card border-brand-200 bg-gradient-to-b from-white to-brand-50/30 p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <UserAvatar :name="forum.currentThread.user?.name" size="md" />
                        <div>
                            <span class="block text-sm font-medium text-ink-800">{{ forum.currentThread.user?.name }}</span>
                            <span class="text-xs text-ink-400">{{ timeAgo(forum.currentThread.created_at) }}</span>
                        </div>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <router-link
                            v-if="canEdit"
                            :to="{ name: 'threads.edit', params: { slug: forum.currentThread.slug } }"
                            class="btn-ghost !px-3 !py-1.5 text-sm"
                        >
                            Edit
                        </router-link>
                        <button
                            v-if="isOwner || auth.isModerator"
                            type="button"
                            class="btn-ghost !px-3 !py-1.5 text-sm text-red-600"
                            @click="onDelete"
                        >
                            Delete
                        </button>
                        <button
                            v-if="auth.isAuthenticated && !isOwner"
                            type="button"
                            class="btn-ghost !px-3 !py-1.5 text-sm"
                            @click="openReport({ type: 'thread', slug: forum.currentThread.slug })"
                        >
                            Report
                        </button>
                    </div>
                </div>
                <p class="mt-3 text-sm leading-relaxed whitespace-pre-line text-ink-700">{{ forum.currentThread.content }}</p>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-ink-100 pt-3">
                    <VoteButtons
                        :score="forum.currentThread.likes_count ?? 0"
                        :my-vote="forum.currentThread.my_vote ?? 0"
                        @vote="onVote"
                    />
                    <div class="flex gap-1">
                        <button
                            type="button"
                            class="btn-ghost !px-3 !py-1.5 text-sm"
                            :class="forum.currentThread.is_bookmarked && '!text-brand-600'"
                            @click="onBookmark"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 20 20" :fill="forum.currentThread.is_bookmarked ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="M5 3.5h10a1 1 0 0 1 1 1v12.1a.4.4 0 0 1-.63.32L10 13.4l-5.37 3.52a.4.4 0 0 1-.63-.32V4.5a1 1 0 0 1 1-1z" />
                            </svg>
                            {{ forum.currentThread.is_bookmarked ? 'Bookmarked' : 'Bookmark' }}
                        </button>
                        <button
                            type="button"
                            class="btn-ghost !px-3 !py-1.5 text-sm"
                            :class="forum.currentThread.is_following && '!text-brand-600'"
                            @click="onFollow"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 20 20" :fill="forum.currentThread.is_following ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="M10 2.5a5 5 0 0 0-5 5v3l-1.4 2.6a.6.6 0 0 0 .53.9h11.74a.6.6 0 0 0 .53-.9L15 10.5v-3a5 5 0 0 0-5-5z" />
                                <path d="M8.5 15.5a1.5 1.5 0 0 0 3 0" />
                            </svg>
                            {{ forum.currentThread.is_following ? 'Following' : 'Follow' }}
                        </button>
                    </div>
                </div>
            </article>

            <h2 class="mt-8 mb-3 text-sm font-semibold tracking-wide text-ink-500 uppercase">
                {{ forum.posts.length }} {{ forum.posts.length === 1 ? 'reply' : 'replies' }}
            </h2>

            <div class="space-y-3">
                <PostCard
                    v-for="post in forum.posts"
                    :key="post.id"
                    :post="post"
                    :is-author="post.user?.id === forum.currentThread.user?.id"
                    @report="openReport({ type: 'post', id: post.id })"
                />
            </div>

            <div v-if="forum.postsMeta && forum.postsMeta.current_page < forum.postsMeta.last_page" class="mt-4 text-center">
                <button type="button" class="btn-ghost text-sm" @click="forum.loadMorePosts()">
                    Load more replies
                </button>
            </div>

            <div class="mt-6">
                <ReplyBox :disabled="forum.currentThread.is_locked" :send="sendReply" />
            </div>

            <ReportModal :open="reportOpen" :target="reportTarget" @close="reportOpen = false" />
        </template>
    </div>
</template>
