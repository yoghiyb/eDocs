<template>
  <div class="timeline">
    <div class="timeline" v-for="(val, key) in timeline" :key="key">
      <!-- timeline time label -->
      <div class="time-label">
        <span class="bg-red">{{ key }}</span>
      </div>

      <!-- /.timeline-label -->
      <!-- timeline item -->
      <div v-for="(value, i) in val" :key="i">
        <i class="fas" :class="checkIcon(value)"></i>
        <div class="timeline-item">
          <span class="time">
            <i class="fas fa-clock"></i>
            {{ value.created_at }}
          </span>
          <h3 class="timeline-header" v-html="checkType(value)">
            <!-- <a href="#">Support Team</a> sent you an email -->
            <!-- {{ checkType(value) }} -->
          </h3>

          <!-- <div class="timeline-body">
            Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles,
            weebly ning heekya handango imeem plugg dopplr jibjab, movity
            jajah plickers sifteo edmodo ifttt zimbra. Babblely odeo kaboodle
            quora plaxo ideeli hulu weebly balihoo...
          </div>
          <div class="timeline-footer">
            <a class="btn btn-primary btn-sm">Read more</a>
            <a class="btn btn-danger btn-sm">Delete</a>
          </div>-->
        </div>
      </div>
      <!-- END timeline item -->
    </div>
    <div>
      <i class="fas fa-clock bg-gray"></i>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      timeline: [],
    };
  },
  created() {
    this.fetchTimeline();
  },
  methods: {
    fetchTimeline() {
      let endpoint = `${BASE_URL}/logs`;
      axios
        .get(endpoint)
        .then((response) => {
          if (response.status == 200) {
            this.timeline = response.data;
          }
        })
        .catch((error) => console.log(error));
    },
    checkType(item) {
      if (item.type == "file") {
        if (item.action == "create") {
          let after = JSON.parse(item.after);
          if (item.file == null) {
            return `<a href="/user/${item.user_id}/detail" >${item.user_data.username}</a> mengunggah file <a href="/document/${after.id}/detail" >${after.name}</a> (DIHAPUS)`;
          }
          return `<a href="/user/${item.user_id}/detail" >${
            item.user_data.username
          }</a> mengunggah file <a href="/document/${after.id}/detail" >${
            item.file && item.file.name
          }</a>`;
        }
        if (item.action == "update") {
          let after = JSON.parse(item.after);
          let before = JSON.parse(item.before);
          if (item.file == null && item.function == "approve") {
            let approved = JSON.parse(item.after);
            return `<a href="/user/${item.user_id}/detail" >${item.user_data.username}</a> menyetujui file <a href="/document/${after.id}/detail" >${approved.name}</a> (DIHAPUS)`;
          }
          if (item.file && item.function == "approve") {
            return `<a href="/user/${item.user_id}/detail" >${item.user_data.username}</a> menyetujui file <a href="/document/${after.id}/detail" >${item.file.name}</a>`;
          }
          if (item.file == null) {
            return `<a href="/user/${item.user_id}/detail" >${
              item.user_data.username
            }</a> memperbarui file <a href="/document/${after.id}/detail" >${
              after.name == before.name
                ? `${before.name} menjadi ${after.name}`
                : `${after.name}`
            }</a> (DIHAPUS)`;
          }
          return `<a href="/user/${item.user_id}/detail" >${
            item.user_data.username
          }</a> memperbarui file <a href="/document/${after.id}/detail" >${
            item.file && item.file.name
          }</a>`;
        }
        if (item.action == "delete") {
          let oldFile = JSON.parse(item.before);
          return `<a href="/user/${item.user_id}/detail" >${
            item.user_data.username
          }</a> menghapus file <a href="/document/${oldFile.id}/detail" >${
            oldFile && oldFile.name
          }</a>`;
        }
      }
      if (item.type == "user") {
        if (item.action == "create") {
          let after = JSON.parse(item.after);
          if (item.user == null) {
            return `<a href="/user/${item.user_id}/detail" >${item.user_data.username}</a> membuat user ${after.username} (DIHAPUS)`;
          }
          return `<a href="/user/${item.user_id}/detail" >${
            item.user_data.username
          }</a> membuat user <a href="/user/${after.id}/detail" >${
            item.user && item.user.username
          }</a>`;
        }
        if (item.action == "update") {
          let after = JSON.parse(item.after);
          let before = JSON.parse(item.before);
          if (item.user == null) {
            return `<a href="/user/${item.user_id}/detail" >${
              item.user_data.username
            }</a> memperbarui user ${
              after.username == before.username
                ? `${before.username} menjadi <a href="/user/${after.id}/detail" >${after.username}</a>`
                : `<a href="/user/${after.id}/detail" >${after.username}</a>`
            } (DIHAPUS) `;
          }
          return `<a href="/user/${item.user_id}/detail" >${
            item.user_data.username
          }</a> memperbarui user <a href="/user/${after.id}/detail" >${
            item.user && item.user.username
          }</a>`;
        }
        if (item.action == "delete") {
          let oldUser = JSON.parse(item.before);
          return `<a href="/user/${item.user_id}/detail" >${item.user_data.username}</a> menghapus user <a href="/user/${oldUser.id}/detail" >${oldUser.username}</a>`;
        }
      }
      if (item.type == "tag") {
        if (item.action == "create") {
          let after = JSON.parse(item.after);
          if (item.tag == null) {
            return `<a href="/user/${item.user_id}/detail" >${item.user_data.username}</a> membuat tag ${after.name} (DIHAPUS)`;
          }
          return `<a href="/user/${item.user_id}/detail" >${item.user_data.username}</a> membuat tag ${tag.name}`;
        }
        if (item.action == "delete") {
          let oldTag = JSON.parse(item.before);
          return `<a href="/user/${item.user_id}/detail" >${
            item.user_data.username
          }</a> menghapus tag ${oldTag && oldTag.name}`;
        }
      }
      if (item.type == "comment") {
        if (item.action == "create") {
          let after = JSON.parse(item.after);
          if (item.function == "reply") {
            if (item.comment == null) {
              return `<a href="/user/${item.user_id}/detail" >${
                item.user_data.username
              }</> membalas komentar ${after.to_user.username} pada ${
                after.comment_owner.split("_")[0] == "doc" ? "file" : "user"
              } <a href="/document/${after.document.id}/detail" >${
                after.document.name
              }</a> (DIHAPUS)`;
            }
            return `<a href="/user/${item.user_id}/detail" >${
              item.user_data.username
            }</a> membalas komentar ${item.comment.to_user.username} pada ${
              item.comment.comment_owner.split("_")[0] == "doc"
                ? "file"
                : "user"
            } <a href="/document/${item.comment.document.id}/detail" >${
              item.comment.document.name
            }</a>`;
          }
          if (item.comment == null) {
            return `<a href="/user/${item.user_id}/detail" >${
              item.user_data.username
            }</a> mengomentari ${
              after.comment_owner.split("_")[0] == "doc" ? "file" : "user"
            } <a href="/document/${after.document.id}/detail" >${
              after.document.name
            }</a> (DIHAPUS)`;
          }
          return `<a href="/user/${item.user_id}/detail" >${
            item.user_data.username
          }</a> mengomentari ${
            item.comment.comment_owner.split("_")[0] == "doc" ? "file" : "user"
          } <a href="/document/${item.comment.document.id}/detail" >${
            item.comment.document.name
          }</a> `;
        }
        if (item.action == "update") {
          let after = JSON.parse(item.after);
          let before = JSON.parse(item.before);
          if (item.comment == null) {
            return `<a href="/user/${item.user_id}/detail" >${
              item.user_data.username
            }</a> memperbarui komentarnya pada ${
              after.comment_owner.split("_")[0] == "doc" ? "file" : "user"
            } <a href="/document/${after.document.id}/detail" >${
              after.document.name
            }</a> (DIHAPUS)`;
          }
          return `<a href="/user/${item.user_id}/detail" >${
            item.user_data.username
          }</a> memperbarui komentarnya pada ${
            item.comment.comment_owner.split("_")[0] == "doc" ? "file" : "user"
          } <a href="/document/${item.comment.document.id}/detail" >${
            item.comment.document.name
          }</a> `;
        }
        if (item.action == "delete") {
          let oldComment = JSON.parse(item.before);
          return `<a href="/user/${item.user_id}/detail" >${
            item.user_data.username
          }</a> menghapus komentar pada ${
            oldComment.comment_owner.split("_")[0] == "doc" ? "file" : "user"
          } <a href="/document/${oldComment.document.id}/detail" >${
            oldComment.document.name
          }</a>`;
        }
      }
    },
    goToUserProfile(id) {
      this.$router.push({
        name: "UserDetail",
        params: { id },
      });
    },
    goToFile() {},
    checkIcon(item) {
      if (item.type == "file") {
        if (item.action == "create") {
          return `fa-file-upload bg-blue`;
        }
        if (item.action == "update") {
          return `fa-file-signature bg-orange`;
        }
        if (item.action == "delete") {
          return `fa-file-download bg-red`;
        }
      }
      if (item.type == "user") {
        if (item.action == "create") {
          return `fa-user-plus bg-blue`;
        }
        if (item.action == "update") {
          return `fa-user-edit bg-orange`;
        }
        if (item.action == "delete") {
          return `fa-user-minus bg-red`;
        }
      }
      if (item.type == "tag") {
        return `fa-tag ${item.action == "create" ? "bg-blue" : "bg-red"}`;
      }
      if (item.type == "comment") {
        if (item.action == "create") {
          if (item.function == "reply") {
            return `fa-comments bg-blue`;
          }
          return `fa-comment bg-blue`;
        }
        if (item.action == "update") {
          return `fa-comment bg-orange`;
        }
        if (item.action == "delete") {
          return `fa-comment-slash bg-red`;
        }
      }
    },
  },
};
</script>

<style>
</style>
