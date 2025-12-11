document.addEventListener('DOMContentLoaded', function() {
    const uploadBtn = document.getElementById('uploadAvatarBtn');
    const avatarInput = document.getElementById('avatarInput');
    const profileImage = document.getElementById('profileImage');

    if (!uploadBtn || !avatarInput) return;

    uploadBtn.addEventListener('click', function() {
        avatarInput.click();
    });

    avatarInput.addEventListener('change', function() {
        const file = avatarInput.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('avatar', file);

        const toast = new Toast();
        fetch('php/upload_avatar.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                toast.show('Profile picture uploaded', 'success', { title: 'Avatar' });
                if (profileImage) {
                    profileImage.src = data.url + '?t=' + Date.now();
                }
            } else {
                toast.show(data.message || 'Upload failed', 'error', { title: 'Avatar' });
            }
        })
        .catch(err => {
            console.error('Avatar upload error', err);
            toast.show('Upload error', 'error', { title: 'Avatar' });
        });
    });
});