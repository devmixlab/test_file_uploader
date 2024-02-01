<script>
    export default {
        watch: {
            chunks(n, o) {
                // console.log(n.length);
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
                chunks: [],
                uploaded: 0,
                siteAvailable: true,
            };
        },

        computed: {
            progress() {
                if(!this.file)
                    return 0;

                return Math.floor((this.uploaded * 100) / this.file.size);
            },
            formData() {
                let formData = new FormData;

                console.log(this.chunks)

                formData.set('is_last', this.chunks.length === 1);
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
                    onUploadProgress: event => {
                        this.uploaded += event.loaded;
                    }
                };
            }
        },

        methods: {
            select(event) {
                this.file = event.target.files.item(0);
                // console.log(this.file);
                this.createChunks();
            },
            upload() {
                // console.log(323);
                axios(this.config).then(response => {
                    // let chunks = this.chunks;
                    let chunks = [].concat(this.chunks);
                    chunks.shift();
                    // this.$set(this.chunks, 'x', (this.arr[1].foo.x || 0) + 100)
                    this.chunks = [].concat(chunks);
                }).catch(error => {
                    this.startCheckingNetworkAvailability();
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
                let size = 20000, chunks = Math.ceil(this.file.size / size);

                console.log(chunks);

                let chunksArr = [];

                for (let i = 0; i < chunks; i++) {
                    chunksArr.push(this.file.slice(
                        i * size, Math.min(i * size + size, this.file.size), this.file.type
                    ));
                }

                this.chunks = chunksArr;
                // console.log(chunksArr);
            }
        }
    }

</script>

<template>

    <div>
        <h1>File Uploader:</h1>
        <input type="file" @change="select">
<!--        <progress :value="progress"></progress>-->
    </div>

</template>
