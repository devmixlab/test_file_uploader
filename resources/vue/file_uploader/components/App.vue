<script>
    export default {
        watch: {
            chunks(n, o) {
                // this.isFirstChunk = n.length > 0 && o.length == 0;

                if (n.length > 0 && this.siteAvailable) {
                    this.upload();
                }
            },
            siteAvailable(n, o) {
                if(n)
                    this.chunks = [].concat(this.chunks);
            }
        },

        data() {
            return {
                file: null,
                fileName: null,
                chunks: [],
                siteAvailable: true,
                chunkSize: 40000,
                // chunkSize: 20000000,
                uploadedFileData: null,
                isFirstChunk: false,
            };
        },

        computed: {
            formData() {
                let formData = new FormData;

                if(this.fileName !== null)
                    formData.set('name', this.fileName);

                if(this.isFirstChunk && this.chunks.length === 1){
                    formData.set('sequence', 'first_last');
                }else if(this.isFirstChunk){
                    formData.set('sequence', 'first');
                }else if(this.chunks.length === 1){
                    formData.set('sequence', 'last');
                }else{
                    formData.set('sequence', 'middle');
                }

                // formData.set('sequence', this.isFirstChunk);
                // formData.set('is_first', this.isFirstChunk);
                // formData.set('is_last', this.chunks.length === 1);
                formData.set('file', this.chunks[0], `${this.file.name}.part`);

                return formData;
            },
            config() {
                return {
                    method: 'POST',
                    data: this.formData,
                    url: 'api/upload_file',
                    headers: {
                        'Content-Type': 'application/octet-stream'
                    },
                };
            }
        },

        methods: {
            select(event) {
                this.isFirstChunk = true;
                this.uploadedFileData = null;
                this.file = event.target.files.item(0);
                this.createChunks();
            },
            reset(chunks = false) {
                this.file = null;
                this.fileName = null;
                this.uploadedFileData = null;
                this.$refs.form.reset();
                if(chunks)
                    this.chunks = [];
            },
            upload() {
                axios(this.config).then(response => {
                    if(this.isFirstChunk)
                        this.isFirstChunk = false;

                    if(typeof response.data.sequence !== undefined && ['first_last','last'].includes(response.data.sequence) && typeof response.data.file !== undefined){
                        this.reset();
                        this.uploadedFileData = response.data.file;
                        return;
                    }

                    if(typeof response.data.sequence !== undefined && response.data.sequence == 'first' && typeof response.data.name !== undefined){
                        this.fileName = response.data.name;
                    }

                    this.chunks.shift();
                    this.chunks = [].concat(this.chunks);
                }).catch(error => {
                    if(typeof error.code !== 'undefined' && error.code == "ERR_NETWORK"){
                        this.startCheckingNetworkAvailability();
                    }else{
                        this.reset(true);
                    }
                });
            },
            startCheckingNetworkAvailability() {
                this.siteAvailable = false;
                let interval = setInterval(() => {
                    axios.get("").then(response => {
                        this.siteAvailable = true;
                        clearInterval(interval);
                    });
                },1000);
            },
            createChunks() {
                let chunks = Math.ceil(this.file.size / this.chunkSize);

                let chunksArr = [];

                for (let i = 0; i < chunks; i++) {
                    chunksArr.push(this.file.slice(
                        i * this.chunkSize, Math.min(i * this.chunkSize + this.chunkSize, this.file.size), this.file.type
                    ));
                }

                this.chunks = chunksArr;
            }
        }
    }

</script>

<template>

    <div>
        <h1>File Uploader:</h1>
        <form ref="form">
            <input ref="file" type="file" @change="select">
        </form>

        <div class="uploaded-file">
            <b>Uploaded file:</b>
            <small v-if="!uploadedFileData">
                no file
            </small>
            <a v-else :href="uploadedFileData.path" target="_blank">{{ uploadedFileData.client_name }}</a>
        </div>
    </div>

</template>
