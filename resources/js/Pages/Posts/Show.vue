<script setup>
import {computed, ref} from "vue";
import CommentForm from "@/Components/CommentForm.vue";
import Post from "@/Components/Post.vue";
import Comment from "@/Components/Comment.vue";
import {useInfiniteScroll} from "@/Composables/useInfiniteScroll.js";
import Shell from "@/Components/Shell.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import {useConfirm} from "@/Composables/useConfirm.js";
import ListItem from "@/Components/ListItem.vue";

const props = defineProps({
   post: Object,
   comments: Object,
   csrf_token: String
});

const commentForm = useForm({
    body: '',
    images: [],
});

const loader = ref(null);
const {items, canLoadMoreItems} = useInfiniteScroll('comments', loader);

const newComment = ref(null);
window.Echo.channel('comments.' + props.post.id)
    .listen('CommentCreated', (comment) => {
        newComment.value = comment;
        items.value = [newComment.value.comment ,...items.value];
    });

const commentIdBeingEdited = ref(null);
const commentBeingEdited = computed(() => items.value.find(comment => comment.id === commentIdBeingEdited.value));

const editArea = ref(null);
const editComment = (commentId) => {
    window.history.replaceState({}, '', props.comments.meta.path);
    commentForm.reset();
    commentForm.clearErrors();
    commentIdBeingEdited.value = commentId;
    commentForm.body = commentBeingEdited.value?.body;
    commentForm.images = commentBeingEdited.value?.images;
    editArea.value.$el.scrollIntoView({block: "center", behavior: "smooth" });
    editArea.value.commentTextAreaRef?.focus();
}

const deleteImage = (index) => {
    return commentForm.delete(route('image.destroy', commentForm.images[index]),
        {
            preserveScroll: true,
            onSuccess: () => {
                commentForm.images.splice(index, 1);
            },
        },
    );
}

const cancelEditComment = () => {
    commentIdBeingEdited.value = null;
    commentForm.reset();
};

let commentFormKey = ref(0);

const addComment = () => {
    return commentForm.post(route('posts.comments.store', props.post.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                commentForm.reset();
                items.value = [props.comments.data[0] ,...items.value];
                commentFormKey.value += 1;
            },
        },
    );
    // axios({
    //     method: 'post',
    //     url: 'comments',
    //     data: {
    //         body: commentForm.body,
    //         images: commentForm.images,
    //     }
    // }).then(function () {
    //     commentForm.reset();
    //     items.value = [props.comments.data[0] ,...items.value];
    //     commentFormKey.value += 1;
    //     usePage().props.jetstream.flash.bannerStyle = 'success';
    //     usePage().props.jetstream.flash.banner = 'Comment Added.';
    // });
};

const { confirmation } = useConfirm();

const updateComment = async () => {
    if (!await confirmation('Are you sure you want to update this comment?')) {
        editArea.value.commentTextAreaRef?.focus();
        return;
    }

    return commentForm.put(route('comments.update', {
            comment: commentIdBeingEdited.value,
            page: props.comments.meta.current_page
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                window.history.replaceState({}, '', props.comments.meta.path);
                for (const [commentkey, comment] of Object.entries(items.value)) {
                    if (comment.id === commentIdBeingEdited.value) {
                        items.value[commentkey].body = commentForm.body;
                        if (commentForm.images?.length) {
                            let counter = 0;
                            for (const [Key, element] of Object.entries(commentForm.images)) {
                                if (!element.id) {
                                    items.value[commentkey].images.push({
                                        "id": usePage().props.flash?.info[counter],
                                        "name": element
                                    });
                                    counter++;
                                }
                            }
                        }
                    }
                }
                commentForm.reset();
                cancelEditComment();
                commentFormKey.value += 1;
            },
        },
    );
};

const deleteComment = async (commentId) => {
    if (! await confirmation('Are you sure you want to delete this comment?')){
        return;
    }

    router.delete(route('comments.destroy', { comment: commentId, page: props.comments.meta.current_page}), {
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
            window.history.replaceState({}, '', props.comments.meta.path);
            for (const [key, comment] of Object.entries(items.value)) {
                if (comment.id === commentId)  {
                    items.value.splice(key, 1);
                }
            }
        },
    });
};
</script>

<template>
    <Shell :title="post.title">
        <Post :post="post"/>
        <div class="mt-5">
            <p class="mt-5 text-2xl font-bold">Comments</p>
            <ListItem v-show="! $page.props.auth.user">
                <div class="sm:flex justify-center my-3 mx-3">
                    <p class="text-xl">
                        <Link class="text-midnight-200 text-xl hover:bg-gray-700 hover:text-white rounded-md px-1 py-1.5 text-sm font-medium" :href="route('login')">Login</Link>
                        or
                        <Link class="text-midnight-200 text-xl hover:bg-gray-700 hover:text-white rounded-md px-1 py-1.5 text-sm font-medium" :href="route('register')">Register</Link>
                        to add your comment now!
                    </p>
                </div>
            </ListItem>
            <CommentForm ref="editArea"
                         @deleteImage="deleteImage"
                         @add="addComment"
                         @update="updateComment"
                         @cancelEdit="cancelEditComment"
                         v-show="$page.props.auth.user"
                         :post="post"
                         :comment-form="commentForm"
                         :inEditMode="!!commentIdBeingEdited"
                         :csrf_token="props.csrf_token"
                         :key="commentFormKey"
            />
            <transition-group
                enter-active-class="transition duration-900 ease-out"
                enter-from-class="transform scale-60 opacity-50"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-900 ease-out"
                leave-from-class="transform scale-100 opacity-50"
                leave-to-class="transform scale-95 opacity-0"
            >
            <Comment @edit="editComment"
                     @delete="deleteComment"
                     v-for="comment in items"
                     :comment="comment"
                     :key="comment.id"
            ></Comment>
            </transition-group>
            <div ref="loader" class="flex justify-center mt-10">
                <v-icon v-if="canLoadMoreItems" name="fa-spinner" scale="2" animation="spin"/>
            </div>
        </div>
    </Shell>
</template>
