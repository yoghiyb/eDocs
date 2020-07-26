<template>
  <div class="card">
    <div class="card-body">
      <h3>Comments</h3>
      <div class="container-fluid">
        <div
          class="row border-bottom border-light pb-2 mt-3"
          v-for="(comment,index) in checkParentComment(comments)"
          :key="index"
        >
          <div class="col-md-2 col-sm-12">
            <span
              class="text-capitalize font-weight-bold mr-2"
            >{{comment.from_user && comment.from_user.username ? comment.from_user.username : '-'}}</span>
            <span
              class="badge"
              :class="[comment.from_id == '1' ? ' badge-success' : comment.from_id == '2' ? ' badge-warning' : ' badge-primary']"
            >{{comment.from_id == '1' ? 'Admin' : comment.from_id == '2' ? 'Manager' : 'Staff'}}</span>&nbsp;:
          </div>
          <div class="col-md-10 col-sm-12">
            <textarea
              v-if="editComment.comment_id == comment.id"
              class="form-control mt-2"
              v-model="editComment.updateComment"
            ></textarea>
            <p v-else class="text-capitalize">{{comment.comment}}</p>
            <textarea
              v-if="reply_to.parent_id == comment.id"
              class="form-control mt-2"
              v-model="reply_to.replyComment"
            ></textarea>
            <div v-if="reply_to.parent_id == comment.id" class="d-flex flex-row mt-2">
              <a class="badge badge-primary text-light cursor-pointer" @click="sendReply()">Reply</a>
              <a
                class="badge badge-secondary text-light cursor-pointer ml-2"
                @click="cancelReply()"
              >Cancel Reply</a>
            </div>
            <div v-else-if="editComment.comment_id == comment.id" class="d-flex flex-row mt-2">
              <a
                class="badge badge-primary text-light cursor-pointer"
                @click="updateThisComment(comment.id)"
              >Submit</a>
              <a
                class="badge badge-secondary text-light cursor-pointer ml-2"
                @click="cancelEdit()"
              >Cancel Edit</a>
            </div>
            <div v-else class="d-flex flex-row mt-2">
              <a
                class="badge badge-primary text-light cursor-pointer"
                @click="replyTo(comment)"
              >Reply</a>
              <a
                v-if="comment.from_id == $root.$data.authUser.id"
                class="badge badge-secondary text-light cursor-pointer ml-2"
                @click="editMyComment(comment)"
              >Edit</a>
              <a
                v-if="comment.from_id == $root.$data.authUser.id"
                class="badge badge-danger text-light cursor-pointer ml-2"
              >Delete</a>
            </div>
            <div v-for="(reComment,index) in checkChildComment(comments)" :key="index">
              <div v-if="reComment.parent_id == comment.id" class="row mt-2">
                <div class="col-sm-12 col-md-2">
                  <span
                    class="text-capitalize font-weight-bold mr-2"
                  >{{reComment.from_user && reComment.from_user.username ? reComment.from_user.username : '-'}}</span>
                  <span
                    class="badge"
                    :class="[reComment.from_id == '1' ? ' badge-success' : reComment.from_id == '2' ? ' badge-warning' : ' badge-primary']"
                  >{{reComment.from_id == '1' ? 'Admin' : reComment.from_id == '2' ? 'Manager' : 'Staff'}}</span>&nbsp;:
                </div>
                <div class="col-md-10 col-sm-12">
                  <p class="text-capitalize">{{reComment.comment}}</p>
                  <div class="d-flex flex-row">
                    <a class="badge badge-secondary text-light cursor-pointer">Edit</a>
                    <a class="badge badge-danger text-light cursor-pointer ml-2">Delete</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group mt-4" id="comment-form">
        <textarea name="comment" id="comment" class="form-control" v-model="newComment"></textarea>
        <button class="btn btn-sm btn-primary mt-2" @click="sendComment()">Comment</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ["owner"],
  data() {
    return {
      reply_to: {
        parent_id: "",
        to_id: "",
        from_id: "",
        replyComment: "",
      },
      editComment: {
        comment_id: "",
        updateComment: "",
      },
      newComment: "",
      comments: [],
    };
  },
  created() {
    this.fetchComment();
  },
  methods: {
    async fetchComment() {
      console.log("dari comment");
      try {
        let endpoint = `${BASE_URL}/comment/${this.owner}`;
        let response = await axios.get(endpoint);

        if (response.status == 200) {
          this.comments = response.data;
        }
      } catch (error) {
        console.log(error);
      }
    },
    replyTo(comment) {
      this.reply_to.parent_id = comment.id;
      this.reply_to.to_id = comment.from_id;
      this.reply_to.from_id = this.$root.$data.authUser.id;
    },
    cancelReply() {
      this.reply_to.parent_id = "";
      this.reply_to.to_id = "";
      this.reply_to.from_id = "";
      this.reply_to.replyComment = "";
    },
    async sendReply() {
      if (this.reply_to.replyComment == "") return;

      try {
        let endpoint = `${BASE_URL}/comment/reply`;
        let { parent_id, to_id, from_id, replyComment } = this.reply_to;
        let body = {
          parent_id: parent_id,
          to_id: to_id,
          from_id: from_id,
          comment_owner: this.owner,
          comment: replyComment,
        };

        console.log("reply comment", body);

        let response = await axios.post(endpoint, body);

        if (response.status == 200) {
          this.fetchComment();
          this.reply_to = {
            parent_id: "",
            to_id: "",
            from_id: "",
            replyComment: "",
          };
        }
      } catch (error) {
        console.log(error);
      }
    },
    editMyComment(comment) {
      this.editComment.comment_id = comment.id;
      this.editComment.updateComment = comment.comment;
    },
    cancelEdit() {
      this.editComment.comment_id = "";
      this.editComment.updateComment = "";
    },
    checkParentComment(comments) {
      //   let a = comments.filter((comment) => comment.parent_id == null);
      //   console.log(a);
      return comments.filter((comment) => comment.parent_id == null);
    },
    checkChildComment(comments) {
      let a = comments.filter((comment) => comment.parent_id != null);
      console.log("reply comment", a);
      return comments.filter((comment) => comment.parent_id != null);
    },
    async updateThisComment(id) {
      if (this.editComment.updateComment == "") return;

      try {
        let endpoint = `${BASE_URL}/comment/${id}`;

        let body = {
          comment: this.editComment.updateComment,
        };

        let response = await axios.put(endpoint, body);

        if (response.status == 200) {
          this.fetchComment();
          this.editComment = {
            comment_id: "",
            updateComment: "",
          };
        }
      } catch (error) {}
    },
    async sendComment() {
      if (this.newComment == "") return;

      let to_id = this.owner.split("_")[1];
      let body = {
        comment: this.newComment,
        from_id: this.$root.$data.authUser.id,
        to_id: parseInt(to_id),
        comment_owner: this.owner,
      };

      //   console.log(body);

      try {
        let endpoint = `${BASE_URL}/comment`;
        let response = await axios.post(endpoint, body);

        if (response.status == 200) {
          this.fetchComment();
          this.newComment = "";
        }
      } catch (error) {
        console.log(error);
      }
    },
  },
};
</script>

<style scoped>
p {
  margin-bottom: 0px;
}

.cursor-pointer {
  cursor: pointer;
}
</style>
