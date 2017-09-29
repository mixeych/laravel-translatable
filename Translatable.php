<?php
namespace App;


trait Translatable
{
    public function __get($name)
    {
        if(!is_array($this->translatable)||!in_array($name, $this->translatable)){
            return parent::__get($name);
        }

        $locale = \App::getLocale();
        if($locale == config('app.fallback_locale')){
            return parent::__get($name);
        }

        $res = \DB::table($this->translatableTable)
            ->select($name)
            ->where('locale', $locale)
            ->where($this->translatableKey, $this->id)
            ->first();
        if(!$res){
            return parent::__get($name);
        }
        return $res->$name;
    }

    public function __set($name, $value)
    {
        if(!is_array($this->translatable)||!in_array($name, $this->translatable)){
            return parent::__set($name, $value);
        }

        $locale = \App::getLocale();
        $localeConf = config('app.fallback_locale');
        if($locale == $localeConf){
            return parent::__set($name, $value);
        }

        $res = \DB::table($this->translatableTable)
            ->select($name,'id')
            ->where('locale', $locale)
            ->where($this->translatableKey, $this->id)
            ->first();
        if(!$res){
            \DB::table($this->translatableTable)->insert([
                [$this->translatableKey => $this->id, $name => $value, 'locale' => $locale],
            ]);
        }else{
            \DB::table($this->translatableTable)->where('id', $res->id)
            ->update([$name => $value]);
        }
    }
}