<?php
class TagsController
{
    public function index()
    {
        $pageTitle = 'Tags';
        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/tags/index.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }

    public function create()
    {
        $pageTitle = 'Nova Tag';
        $tagId = null;
        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/tags/form.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }

    public function edit($id)
    {
        $pageTitle = 'Editar Tag';
        $tagId = (int)$id;
        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/tags/form.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }
}
